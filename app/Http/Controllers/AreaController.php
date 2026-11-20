<?php

namespace App\Http\Controllers;

use App\Models\Area;
use Illuminate\Http\Request;

class AreaController extends Controller
{
    /**
     * Display a listing of the areas.
     */
    public function index(Request $request)
    {
        $query = Area::query();

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('status', 'like', "%{$search}%");
            });
        }

        $areas = $query->orderBy('id', 'asc')->paginate(10)->withQueryString();

        return view('areas.index', compact('areas'));
    }

    /**
     * Show the form for creating a new area.
     */
    public function create()
    {
        $existingAreas = Area::pluck('name')->map(fn($n) => strtolower(trim($n)))->toArray();
        return view('areas.create', compact('existingAreas'));
    }

    /**
     * Check if an area name already exists (AJAX duplicacy check).
     */
    public function checkName(Request $request)
    {
        $name = trim($request->input('name', ''));
        $ignoreId = $request->input('ignore_id');

        $query = Area::whereRaw('LOWER(name) = ?', [strtolower($name)]);
        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        return response()->json([
            'exists' => $query->exists()
        ]);
    }

    /**
     * Store a newly created area in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:areas,name',
            'description' => 'nullable|string',
        ]);

        $validated['status'] = 'Active'; // Automatically active on creation

        Area::create($validated);

        return redirect()->route('areas.index')->with('success', 'Area created successfully.');
    }

    /**
     * Display the specified area.
     */
    public function show(Area $area)
    {
        return view('areas.show', compact('area'));
    }

    /**
     * Show the form for editing the specified area.
     */
    public function edit(Area $area)
    {
        $existingAreas = Area::where('id', '!=', $area->id)
            ->pluck('name')
            ->map(fn($n) => strtolower(trim($n)))
            ->toArray();
        return view('areas.edit', compact('area', 'existingAreas'));
    }

    /**
     * Update the specified area in storage.
     */
    public function update(Request $request, Area $area)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:areas,name,' . $area->id,
            'status' => 'required|string|in:Active,Inactive',
            'description' => 'nullable|string',
        ]);

        $area->update($validated);

        return redirect()->route('areas.index')->with('success', 'Area updated successfully.');
    }

    /**
     * Remove the specified area from storage.
     */
    public function destroy(Area $area)
    {
        $area->delete();

        return redirect()->route('areas.index')->with('success', 'Area deleted successfully.');
    }
}

