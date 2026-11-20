<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Location;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    /**
     * Display a listing of the locations.
     */
    public function index(Request $request)
    {
        $query = Location::with('area');

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('location_name', 'like', "%{$search}%")
                    ->orWhere('location_type', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%")
                    ->orWhereHas('area', function ($areaQuery) use ($search) {
                        $areaQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $locations = $query->orderBy('id', 'asc')->paginate(10)->withQueryString();

        return view('locations.index', compact('locations'));
    }

    /**
     * Show the form for creating a new location.
     */
    public function create()
    {
        $areas = Area::where('status', 'Active')->orderBy('name', 'asc')->get();
        // If no active areas found, fetch all areas
        if ($areas->isEmpty()) {
            $areas = Area::orderBy('name', 'asc')->get();
        }

        $locationTypes = ['In', 'Out', 'Warehouse'];

        return view('locations.create', compact('areas', 'locationTypes'));
    }

    /**
     * Store a newly created location in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'area_id' => 'required|exists:areas,id',
            'location_name' => 'required|string|max:255',
            'location_type' => 'required|in:In,Out,Warehouse',
            'status' => 'nullable|string|in:Active,Inactive',
        ]);

        if (empty($validated['status'])) {
            $validated['status'] = 'Active';
        }

        Location::create($validated);

        $msg = strtoupper($validated['location_name']) . ' Location Added Successfully';

        return redirect()->route('locations.index')->with('success', $msg);
    }

    /**
     * Display the specified location.
     */
    public function show(Location $location)
    {
        $location->load('area', 'devices');

        return view('locations.show', compact('location'));
    }

    /**
     * Show the form for editing the specified location.
     */
    public function edit(Location $location)
    {
        $areas = Area::orderBy('name', 'asc')->get();
        $locationTypes = ['In', 'Out', 'Warehouse'];

        return view('locations.edit', compact('location', 'areas', 'locationTypes'));
    }

    /**
     * Update the specified location in storage.
     */
    public function update(Request $request, Location $location)
    {
        $validated = $request->validate([
            'area_id' => 'required|exists:areas,id',
            'location_name' => 'required|string|max:255',
            'location_type' => 'required|in:In,Out,Warehouse',
            'status' => 'required|string|in:Active,Inactive',
        ]);

        $location->update($validated);

        $msg = strtoupper($validated['location_name']) . ' Location Updated Successfully';

        return redirect()->route('locations.index')->with('success', $msg);
    }

    /**
     * Remove the specified location from storage.
     */
    public function destroy(Location $location)
    {
        $location->delete();

        return redirect()->route('locations.index')->with('success', 'Location deleted successfully.');
    }
}
