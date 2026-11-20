<?php

namespace App\Http\Controllers;

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

    /*public function __construct()
    {
        $this->middleware('auth');
    }*/

    public function index(Request $request): View
    {
        $query = DB::table('assets');

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('serial_no', 'like', "%{$search}%")
                    ->orWhere('asset_code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category')) {
            $category = trim((string) $request->input('category'));
            $query->where('category', 'like', "%{$category}%");
        }

        $status = strtolower(trim((string) $request->input('status')));
        if ($request->filled('status') && in_array($status, self::ALLOWED_STATUSES, true)) {
            $query->where('status', $status);
        }

        if ($request->filled('location_id')) {
            $query->where('location_id', $request->input('location_id'));
        }

        $assets = $query
            ->orderByDesc('last_seen_at')
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $locations = DB::table('locations')->orderBy('id')->get();

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
        $assets = DB::table('assets')->orderBy('name')->get();

        return view('asset.map', compact('assets'));
    }

    public function mapTag(Request $request): JsonResponse
    {
        $request->merge([
            'status'   => $request->filled('status') ? strtolower(trim((string) $request->input('status'))) : null,
            'category' => $request->filled('category') ? trim((string) $request->input('category')) : null,
        ]);

        $validated = $request->validate([
            'asset_id'    => 'required|integer|exists:assets,id',
            'serial_no'   => 'required|string|max:255|unique:assets,serial_no,' . $request->input('asset_id'),
            'name'        => 'nullable|string|max:255',
            'category'    => 'nullable|string|max:100',
            'location_id' => 'nullable|integer|exists:locations,id',
            'status'      => 'nullable|in:available,in_use,maintenance,retired',
            'remarks'     => 'nullable|string|max:1000',
        ]);

        $asset = DB::table('assets')->where('id', $validated['asset_id'])->first();
        $previousSerialNo = $asset?->serial_no;
        $now = now();

        $update = [
            'serial_no'  => $validated['serial_no'],
            'updated_at' => $now,
        ];

        if (!empty($validated['name'])) {
            $update['name'] = $validated['name'];
        }

        if (!is_null($validated['category'])) {
            $update['category'] = $validated['category'] !== '' ? $validated['category'] : null;
        }

        if ($request->has('location_id')) {
            $update['location_id'] = $validated['location_id'] ?? null;
        }

        if (!empty($validated['status'])) {
            $update['status'] = $validated['status'];
        }

        DB::table('assets')
            ->where('id', $validated['asset_id'])
            ->update($update);

        RfidAssignmentLog::create([
            'asset_id'           => (int) $validated['asset_id'],
            'serial_no'          => $validated['serial_no'],
            'previous_serial_no' => $previousSerialNo,
            'action'             => $previousSerialNo && $previousSerialNo !== $validated['serial_no']
                ? 'remapped'
                : 'assigned',
            'assigned_by'        => Auth::id(),
            'remarks'            => $validated['remarks'] ?? 'Mapped via asset/map',
            'assigned_at'        => $now,
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Asset tag and details updated.',
        ]);
    }

    public function categories(): View
    {
        $areas = DB::table('areas')->orderBy('id')->get();
        $locations = DB::table('locations')->orderBy('id')->get();
        $locationTypes = ['In', 'Out', 'Warehouse'];
        /*$categories = DB::table('assets')
            ->select('category', DB::raw('COUNT(*) as total_assets'))
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->groupBy('category')
            ->orderBy('category')
            ->get();*/
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
        $categories = DB::table('assets')
            ->select('category')
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

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
}
