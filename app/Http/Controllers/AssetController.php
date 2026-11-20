<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use App\Models\RfidAssignmentLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AssetController extends Controller
{
    private const ALLOWED_STATUSES = ['available', 'in_use', 'maintenance', 'retired'];

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request): View
    {
        $query = DB::table('assets')
            ->leftJoin('locations', 'assets.location_id', '=', 'locations.id')
            ->select([
                'assets.*',
                'locations.location_name',
            ]);

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('assets.serial_no', 'like', "%{$search}%")
                    ->orWhere('assets.asset_code', 'like', "%{$search}%")
                    ->orWhere('assets.name', 'like', "%{$search}%")
                    ->orWhere('assets.category', 'like', "%{$search}%")
                    ->orWhere('locations.location_name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category')) {
            $category = trim((string) $request->input('category'));
            $query->where('assets.category', 'like', "%{$category}%");
        }

        $status = strtolower(trim((string) $request->input('status')));
        if ($request->filled('status') && in_array($status, self::ALLOWED_STATUSES, true)) {
            $query->where('assets.status', $status);
        }

        if ($request->filled('location_id')) {
            $query->where('assets.location_id', $request->input('location_id'));
        }

        $assets = $query
            ->orderByDesc('assets.last_seen_at')
            ->orderByDesc('assets.updated_at')
            ->orderByDesc('assets.id')
            ->paginate(15)
            ->withQueryString();

        $locations = DB::table('locations')->orderBy('location_name')->get();

        $stats = [
            'total'           => DB::table('assets')->count(),
            'available_count' => DB::table('assets')->where('status', 'available')->count(),
            'in_use_count'    => DB::table('assets')->where('status', 'in_use')->count(),
            'other_count'     => DB::table('assets')->whereNotIn('status', ['available', 'in_use'])->count(),
        ];

        return view('asset.index', compact('assets', 'locations', 'stats'));
    }

    public function mapForm(): View
    {
        $assets = DB::table('assets')
            ->leftJoin('locations', 'assets.location_id', '=', 'locations.id')
            ->select([
                'assets.id',
                'assets.asset_code',
                'assets.name',
                'assets.serial_no',
                'assets.category',
                'assets.status',
                'locations.location_name',
            ])
            ->orderBy('assets.name')
            ->get();

        $locations = DB::table('locations')->orderBy('location_name')->get();
        $categories = DB::table('categories')->orderBy('name')->pluck('name');

        if ($categories->isEmpty()) {
            $categories = DB::table('assets')
                ->select('category')
                ->whereNotNull('category')
                ->where('category', '!=', '')
                ->distinct()
                ->orderBy('category')
                ->pluck('category');
        }

        return view('asset.map', compact('assets', 'locations', 'categories'));
    }

    public function mapTag(Request $request)
    {
        $validated = $request->validate([
            'asset_id'    => 'required|integer|exists:assets,id',
            'serial_no'   => 'required|string|max:255',
            'location_id' => 'nullable|integer|exists:locations,id',
            'category'    => 'nullable|string|max:100',
            'status'      => 'nullable|string|in:' . implode(',', self::ALLOWED_STATUSES),
        ]);

        $serialNo = trim($validated['serial_no']);

        // Prevent mapping the same RFID tag to two different assets
        $conflict = DB::table('assets')
            ->where('serial_no', $serialNo)
            ->where('id', '!=', $validated['asset_id'])
            ->first();

        if ($conflict) {
            $message = "Tag {$serialNo} is already mapped to asset {$conflict->asset_code} ({$conflict->name}).";
            return $request->expectsJson()
                ? response()->json(['message' => $message], 422)
                : back()->withErrors(['serial_no' => $message])->withInput();
        }

        $result = DB::transaction(function () use ($validated, $serialNo) {
            $asset = DB::table('assets')->where('id', $validated['asset_id'])->lockForUpdate()->first();
            $previousSerial = $asset->serial_no;
            $now = now();

            $updateData = [
                'serial_no'    => $serialNo,
                'last_seen_at' => $now,
                'updated_at'   => $now,
            ];

            if (!empty($validated['location_id'])) {
                $updateData['location_id'] = $validated['location_id'];
            }

            if (!empty($validated['category'])) {
                $updateData['category'] = $validated['category'];
            }

            if (!empty($validated['status'])) {
                $updateData['status'] = $validated['status'];
            }

            DB::table('assets')->where('id', $asset->id)->update($updateData);

            $action = $previousSerial === null
                ? 'mapped'
                : ($previousSerial !== $serialNo ? 'reassigned' : 'remapped');

            DB::table('rfid_assignment_logs')->insert([
                'asset_id'          => $asset->id,
                'serial_no'         => $serialNo,
                'previous_serial_no' => $previousSerial,
                'action'            => $action,
                'created_at'        => $now,
                'updated_at'        => $now,
            ]);

            return DB::table('assets')
                ->leftJoin('locations', 'assets.location_id', '=', 'locations.id')
                ->select('assets.*', 'locations.location_name')
                ->where('assets.id', $asset->id)
                ->first();
        });

        if ($request->expectsJson()) {
            return response()->json([
                'message' => "RFID tag {$serialNo} mapped to {$result->name}.",
                'asset'   => $result,
            ]);
        }

        return redirect()->route('asset.map')->with('success', "RFID tag {$serialNo} mapped to {$result->name}.");
    }

    public function categories(): View
    {
        $areas = DB::table('areas')->orderBy('id')->get();
        $locations = DB::table('locations')->orderBy('id')->get();
        $locationTypes = ['In', 'Out', 'Warehouse'];
        $categories = DB::table('categories')->orderBy('name')->get();

        return view('asset.categories', compact('areas', 'locations', 'locationTypes', 'categories'));
    }
    public function storeCategoryName(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:100|unique:categories,name',
            'description' => 'nullable|string|max:500',
        ]);

        DB::table('categories')->insert([
            'name'        => trim($validated['name']),
            'description' => $validated['description'] ?? null,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        return redirect()->route('asset.categories')->with('success', 'Category saved.');
    }
    public function storeCategory(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'area_id'       => 'required|integer|exists:areas,id',
            'location_name' => 'required|string|max:255',
            'location_type' => 'required|string|max:100',
        ]);

        DB::table('locations')->insert([
            'area_id'       => $validated['area_id'],
            'location_name' => $validated['location_name'],
            'location_type' => $validated['location_type'],
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        return redirect()->route('asset.categories')->with('success', 'Location saved.');
    }

    public function create(): View
    {
        $locations = DB::table('locations')->orderBy('id')->get();
        $categories = DB::table('categories')->orderBy('name')->pluck('name');
        if ($categories->isEmpty()) {
            $categories = DB::table('assets')
                ->select('category')
                ->whereNotNull('category')
                ->where('category', '!=', '')
                ->distinct()
                ->orderBy('category')
                ->pluck('category');
        }

        return view('asset.create', compact('locations', 'categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->merge([
            'status'   => strtolower(trim((string) $request->input('status', 'available'))),
            'category' => $request->filled('category') ? trim((string) $request->input('category')) : null,
        ]);

        $validated = $request->validate([
            'serial_no'   => 'required|string|max:255|unique:assets,serial_no',
            'asset_code'  => 'required|string|max:255',
            'name'        => 'required|string|max:255',
            'location_id' => 'nullable|integer|exists:locations,id',
            'category'    => 'nullable|string|max:100',
            'status'      => 'required|in:available,in_use,maintenance,retired',
        ]);

        $now = now();

        $assetId = DB::table('assets')->insertGetId([
            'serial_no'   => $validated['serial_no'],
            'asset_code'  => $validated['asset_code'],
            'name'        => $validated['name'],
            'location_id' => $validated['location_id'] ?? null,
            'category'    => $validated['category'],
            'status'      => $validated['status'],
            'last_seen_at' => null,
            'created_at'  => $now,
            'updated_at'  => $now,
        ]);

        RfidAssignmentLog::create([
            'asset_id'           => $assetId,
            'serial_no'          => $validated['serial_no'],
            'previous_serial_no' => null,
            'action'             => 'assigned',
            'assigned_by'        => Auth::id(),
            'remarks'            => 'Assigned during asset registration',
            'assigned_at'        => $now,
        ]);

        return redirect()->route('asset.index')->with('success', 'Asset registered successfully.');
    }
    public function syncFromLogFile(Request $request)
    {
        $logUrl = $request->input('url', 'https://wavelinx.in/api/log.txt');

        try {
            $response = Http::timeout(15)->get($logUrl);

            if (!$response->successful()) {
                return response()->json([
                    'message' => "Failed to read log file (HTTP {$response->status()}).",
                ], 502);
            }

            $rawText = $response->body();

            // Split by "API Request" blocks and extract the JSON object from each block
            $blocks = preg_split('/^\s*API Request\s*$/m', $rawText);
            $insertedCount = 0;
            $skippedCount = 0;

            DB::transaction(function () use ($blocks, &$insertedCount, &$skippedCount) {
                foreach ($blocks as $block) {
                    $trimmed = trim($block);
                    if ($trimmed === '') {
                        continue;
                    }

                    $firstBrace = strpos($trimmed, '{');
                    $lastBrace = strrpos($trimmed, '}');
                    if ($firstBrace === false || $lastBrace === false || $lastBrace <= $firstBrace) {
                        continue;
                    }

                    $jsonChunk = substr($trimmed, $firstBrace, $lastBrace - $firstBrace + 1);
                    $entry = json_decode($jsonChunk, true);

                    if (!is_array($entry)) {
                        continue;
                    }

                    $scans = $entry['json_body'] ?? [];
                    if (!is_array($scans) && !empty($entry['raw_body']) && is_string($entry['raw_body'])) {
                        $scans = json_decode($entry['raw_body'], true) ?? [];
                    }

                    if (!is_array($scans)) {
                        continue;
                    }

                    foreach ($scans as $scan) {
                        if (!is_array($scan) || empty($scan['epc'])) {
                            continue;
                        }

                        $epc = trim((string) $scan['epc']);
                        $readerId = trim((string) ($scan['readerId'] ?? 'unknown'));
                        $readerName = isset($scan['readerName']) ? trim((string) $scan['readerName']) : null;
                        $antennaNo = isset($scan['antenna']) ? (int) $scan['antenna'] : 1;
                        $rssi = isset($scan['rssi']) ? (float) $scan['rssi'] : null;

                        $readAt = now();
                        if (!empty($scan['timestamp'])) {
                            try {
                                $readAt = Carbon::parse($scan['timestamp'])->setTimezone(config('app.timezone'));
                            } catch (\Throwable $e) {
                                $readAt = now();
                            }
                        }

                        // Avoid inserting duplicate scans for the same EPC, reader, and timestamp
                        $alreadyExists = DB::table('rfid_scan_logs')
                            ->where('epc', $epc)
                            ->where('reader_id', $readerId)
                            ->where('read_at', $readAt->format('Y-m-d H:i:s'))
                            ->exists();

                        if ($alreadyExists) {
                            $skippedCount++;
                            continue;
                        }

                        $asset = DB::table('assets')
                            ->where('serial_no', $epc)
                            ->select('id', 'location_id')
                            ->first();

                        $assetId = $asset->id ?? null;
                        $locationId = $asset->location_id ?? null;

                        $now = now();

                        DB::table('rfid_scan_logs')->insert([
                            'epc'         => $epc,
                            'asset_id'    => $assetId,
                            'reader_id'   => $readerId,
                            'reader_name' => $readerName,
                            'antenna_no'  => $antennaNo,
                            'rssi'        => $rssi,
                            'status'      => null, // Set 'IN' or 'OUT' if mapped by reader/location
                            'location_id' => $locationId,
                            'raw_payload' => json_encode($scan),
                            'read_at'     => $readAt->format('Y-m-d H:i:s'),
                            'created_at'  => $now,
                            'updated_at'  => $now,
                        ]);

                        if ($assetId) {
                            DB::table('assets')
                                ->where('id', $assetId)
                                ->update([
                                    'last_seen_at' => $readAt->format('Y-m-d H:i:s'),
                                    'updated_at'   => $now,
                                ]);
                        }

                        $insertedCount++;
                    }
                }
            });

            return response()->json([
                'message'  => "Synced {$insertedCount} new scan(s) ({$skippedCount} duplicate(s) skipped).",
                'inserted' => $insertedCount,
                'skipped'  => $skippedCount,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Error syncing log file: ' . $e->getMessage(),
            ], 500);
        }
    }
    public function latestScan()
    {
        $latest = DB::table('rfid_scan_logs')
            ->select([
                'id',
                'epc as serial_no',
                'asset_id',
                'reader_id',
                'reader_name',
                'antenna_no',
                'rssi',
                'status',
                'location_id',
                'read_at',
                'created_at',
            ])
            ->orderByDesc('id')
            ->first();

        if (!$latest || empty($latest->serial_no)) {
            return response()->json([
                'message' => 'No recent RFID scan found.',
            ], 404);
        }

        $matchedAssetId = $latest->asset_id;
        if (!$matchedAssetId) {
            $matchedAssetId = DB::table('assets')
                ->where('serial_no', $latest->serial_no)
                ->value('id');
        }

        return response()->json([
            'scan_id'      => $latest->id,
            'serial_no'    => $latest->serial_no,
            'asset_id'     => $matchedAssetId,
            'location_id'  => $latest->location_id,
            'reader_name'  => $latest->reader_name ?: $latest->reader_id,
            'scan_status'  => $latest->status,
            'scanned_at'   => $latest->read_at ?? $latest->created_at,
        ]);
    }
}
