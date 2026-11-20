<?php

namespace App\Http\Controllers;

use App\Models\RfidAssignmentLog;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
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
        $categories = $this->getCategoryNames();

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

            // Link any previously unmapped scan logs for this EPC to the mapped asset
            if (Schema::hasTable('rfid_scan_logs')) {
                DB::table('rfid_scan_logs')
                    ->where('epc', $serialNo)
                    ->whereNull('asset_id')
                    ->update([
                        'asset_id'   => $asset->id,
                        'updated_at' => $now,
                    ]);
            }

            $action = $previousSerial === null
                ? 'mapped'
                : ($previousSerial !== $serialNo ? 'reassigned' : 'remapped');

            DB::table('rfid_assignment_logs')->insert([
                'asset_id'           => $asset->id,
                'serial_no'          => $serialNo,
                'previous_serial_no' => $previousSerial,
                'action'             => $action,
                'created_at'         => $now,
                'updated_at'         => $now,
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
        $categories = $this->getCategoryNames();

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
            'serial_no'    => $validated['serial_no'],
            'asset_code'   => $validated['asset_code'],
            'name'         => $validated['name'],
            'location_id'  => $validated['location_id'] ?? null,
            'category'     => $validated['category'],
            'status'       => $validated['status'],
            'last_seen_at' => null,
            'created_at'   => $now,
            'updated_at'   => $now,
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

    public function edit(int $asset): View
    {
        $item = DB::table('assets')->where('id', $asset)->firstOrFail();
        $locations = DB::table('locations')->orderBy('location_name')->get();
        $categories = $this->getCategoryNames();

        return view('asset.edit', compact('item', 'locations', 'categories'));
    }

    public function update(Request $request, int $asset): RedirectResponse
    {
        $existing = DB::table('assets')->where('id', $asset)->firstOrFail();

        $validated = $request->validate([
            'serial_no'   => 'nullable|string|max:255|unique:assets,serial_no,' . $asset,
            'asset_code'  => 'required|string|max:100|unique:assets,asset_code,' . $asset,
            'name'        => 'required|string|max:255',
            'category'    => 'nullable|string|max:100',
            'location_id' => 'nullable|integer|exists:locations,id',
            'status'      => 'required|string|in:' . implode(',', self::ALLOWED_STATUSES),
        ]);

        $serialNo = isset($validated['serial_no']) && trim($validated['serial_no']) !== ''
            ? trim($validated['serial_no'])
            : null;

        DB::transaction(function () use ($existing, $validated, $serialNo) {
            $now = now();

            DB::table('assets')->where('id', $existing->id)->update([
                'serial_no'   => $serialNo,
                'asset_code'  => trim($validated['asset_code']),
                'name'        => trim($validated['name']),
                'category'    => $validated['category'] ?? null,
                'location_id' => $validated['location_id'] ?? null,
                'status'      => $validated['status'],
                'updated_at'  => $now,
            ]);

            if ($serialNo && $serialNo !== $existing->serial_no) {
                DB::table('rfid_assignment_logs')->insert([
                    'asset_id'           => $existing->id,
                    'serial_no'          => $serialNo,
                    'previous_serial_no' => $existing->serial_no,
                    'action'             => $existing->serial_no === null ? 'mapped' : 'reassigned',
                    'created_at'         => $now,
                    'updated_at'         => $now,
                ]);
            }
        });

        return redirect()->route('asset.index')->with('success', "Asset {$validated['name']} updated successfully.");
    }

    public function destroy(Request $request, int $asset)
    {
        $existing = DB::table('assets')->where('id', $asset)->firstOrFail();

        DB::transaction(function () use ($existing) {
            if (Schema::hasTable('rfid_scan_logs')) {
                DB::table('rfid_scan_logs')
                    ->where('asset_id', $existing->id)
                    ->update(['asset_id' => null, 'updated_at' => now()]);
            }

            DB::table('assets')->where('id', $existing->id)->delete();
        });

        if ($request->expectsJson()) {
            return response()->json(['message' => "Asset {$existing->name} deleted."]);
        }

        return redirect()->route('asset.index')->with('success', "Asset {$existing->name} deleted.");
    }

    public function storeScan(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'epc'         => 'required|string|max:255',
            'asset_id'    => 'nullable|integer|exists:assets,id',
            'reader_id'   => 'nullable|string|max:100',
            'reader_name' => 'nullable|string|max:255',
            'antenna_no'  => 'nullable|integer|min:1|max:255',
            'rssi'        => 'nullable|numeric',
            'status'      => 'nullable|in:IN,OUT',
            'location_id' => 'nullable|integer|exists:locations,id',
            'raw_payload' => 'nullable',
            'read_at'     => 'nullable|date',
        ]);

        $epc = trim($validated['epc']);
        $readerId = trim((string) ($validated['reader_id'] ?? 'unknown'));
        $now = now();
        $readAt = !empty($validated['read_at'])
            ? Carbon::parse($validated['read_at'])->setTimezone(config('app.timezone'))
            : $now;

        $assetId = $validated['asset_id'] ?? DB::table('assets')
            ->where('serial_no', $epc)
            ->value('id');

        $locationId = $validated['location_id'] ?? null;
        if (!$locationId && $assetId) {
            $locationId = DB::table('assets')
                ->where('id', $assetId)
                ->value('location_id');
        }

        $seenBatch = [];
        $status = $validated['status'] ?? $this->resolveScanDirection($readerId, $readAt, $seenBatch);

        $scanId = DB::table('rfid_scan_logs')->insertGetId([
            'epc'         => $epc,
            'asset_id'    => $assetId,
            'reader_id'   => $readerId,
            'reader_name' => $validated['reader_name'] ?? null,
            'antenna_no'  => $validated['antenna_no'] ?? 1,
            'rssi'        => $validated['rssi'] ?? null,
            'status'      => $status,
            'location_id' => $locationId,
            'raw_payload' => isset($validated['raw_payload'])
                ? (is_string($validated['raw_payload']) ? $validated['raw_payload'] : json_encode($validated['raw_payload']))
                : json_encode($request->all()),
            'read_at'     => $readAt->format('Y-m-d H:i:s'),
            'created_at'  => $now,
            'updated_at'  => $now,
        ]);

        if ($assetId) {
            $assetUpdate = [
                'last_seen_at' => $readAt->format('Y-m-d H:i:s'),
                'updated_at'   => $now,
            ];
            if ($locationId) {
                $assetUpdate['location_id'] = $locationId;
            }
            DB::table('assets')->where('id', $assetId)->update($assetUpdate);
        }

        return response()->json([
            'message'  => 'Scan logged successfully.',
            'scan_id'  => $scanId,
            'epc'      => $epc,
            'asset_id' => $assetId,
            'status'   => $status,
        ], 201);
    }

    public function syncFromLogFile(Request $request): JsonResponse
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

            // Split by "API Request" blocks and collect all valid scan items
            $blocks = preg_split('/^\s*API Request\s*$/m', $rawText);
            $parsedScans = [];

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

                    $readAt = now();
                    if (!empty($scan['timestamp'])) {
                        try {
                            $readAt = Carbon::parse($scan['timestamp'])->setTimezone(config('app.timezone'));
                        } catch (\Throwable $e) {
                            $readAt = now();
                        }
                    }

                    $parsedScans[] = [
                        'scan'    => $scan,
                        'read_at' => $readAt,
                    ];
                }
            }

            // Sort chronologically so the first scan of each day is accurately marked IN
            usort($parsedScans, static function (array $a, array $b): int {
                return $a['read_at']->timestamp <=> $b['read_at']->timestamp;
            });

            $insertedCount = 0;
            $skippedCount = 0;
            $seenTodayInBatch = [];

            DB::transaction(function () use ($parsedScans, &$insertedCount, &$skippedCount, &$seenTodayInBatch) {
                foreach ($parsedScans as $item) {
                    $scan = $item['scan'];
                    /** @var Carbon $readAt */
                    $readAt = $item['read_at'];

                    $epc = trim((string) $scan['epc']);
                    $readerId = trim((string) ($scan['readerId'] ?? 'unknown'));
                    $readerName = isset($scan['readerName']) ? trim((string) $scan['readerName']) : null;
                    $antennaNo = isset($scan['antenna']) ? (int) $scan['antenna'] : 1;
                    $rssi = isset($scan['rssi']) ? (float) $scan['rssi'] : null;

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

                    // Resolve IN / OUT via reader_locations table, falling back to first-seen-today rule
                    $status = $this->resolveScanDirection($readerId, $readAt, $seenTodayInBatch);

                    $now = now();

                    DB::table('rfid_scan_logs')->insert([
                        'epc'         => $epc,
                        'asset_id'    => $assetId,
                        'reader_id'   => $readerId,
                        'reader_name' => $readerName,
                        'antenna_no'  => $antennaNo,
                        'rssi'        => $rssi,
                        'status'      => $status,
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

    public function latestScan(): JsonResponse
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
            ->whereNotNull('epc')
            ->where('epc', '!=', '')
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
            'scan_id'     => $latest->id,
            'serial_no'   => $latest->serial_no,
            'asset_id'    => $matchedAssetId,
            'location_id' => $latest->location_id,
            'reader_name' => $latest->reader_name ?: $latest->reader_id,
            'scan_status' => $latest->status,
            'scanned_at'  => $latest->read_at ?? $latest->created_at,
        ]);
    }

    private function getReaderLocationMap(): array
    {
        return Cache::remember('rfid_reader_locations_map', 300, function () {
            if (!Schema::hasTable('reader_locations')) {
                return [];
            }

            $rows = DB::table('reader_locations')
                ->select(['reader_id', 'zone_name', 'default_direction'])
                ->get();

            $map = [];
            foreach ($rows as $row) {
                $key = strtolower(trim((string) $row->reader_id));
                if ($key === '') {
                    continue;
                }

                $dir = strtoupper(trim((string) ($row->default_direction ?? '')));
                $map[$key] = [
                    'direction' => in_array($dir, ['IN', 'OUT'], true) ? $dir : null,
                    'zone_name' => $row->zone_name,
                ];
            }

            return $map;
        });
    }

    private function resolveScanDirection(
        string $readerId,
        Carbon $scanTime,
        array &$seenTodayInBatch = []
    ): string {
        $cleanId = trim($readerId);
        $lookup = $this->getReaderLocationMap()[strtolower($cleanId)] ?? null;

        if ($lookup && !empty($lookup['direction'])) {
            return $lookup['direction'];
        }

        $dateKey = $scanTime->format('Y-m-d');
        $batchKey = $dateKey . '|' . strtolower($cleanId);

        if (!empty($seenTodayInBatch[$batchKey])) {
            return 'OUT';
        }

        $alreadySeenToday = DB::table('rfid_scan_logs')
            ->where('reader_id', $cleanId)
            ->whereDate(DB::raw('COALESCE(read_at, created_at)'), $dateKey)
            ->exists();

        $seenTodayInBatch[$batchKey] = true;

        return $alreadySeenToday ? 'OUT' : 'IN';
    }

    private function getCategoryNames()
    {
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

        return $categories;
    }
}
