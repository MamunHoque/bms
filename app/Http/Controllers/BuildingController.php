<?php

namespace App\Http\Controllers;

use App\Models\Building;
use Illuminate\Http\Request;

class BuildingController extends Controller
{
    public function index()
    {
        $buildings = Building::withCount(['units', 'units as occupied_count' => function ($q) {
            $q->where('status', 'occupied');
        }])->orderBy('name')->paginate(15);

        return view('buildings.index', compact('buildings'));
    }

    public function create()
    {
        return view('buildings.create', ['building' => new Building()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'address' => ['required', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'total_floors' => ['required', 'integer', 'min:1', 'max:200'],
            'notes' => ['nullable', 'string'],
        ]);
        Building::create($data);
        return redirect()->route('buildings.index')->with('status', 'Building created.');
    }

    public function show(Building $building)
    {
        $building->load(['units' => fn ($q) => $q->orderBy('floor')->orderBy('unit_number')]);
        return view('buildings.show', compact('building'));
    }

    public function edit(Building $building)
    {
        return view('buildings.edit', compact('building'));
    }

    public function update(Request $request, Building $building)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'address' => ['required', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'total_floors' => ['required', 'integer', 'min:1', 'max:200'],
            'notes' => ['nullable', 'string'],
        ]);
        $building->update($data);
        return redirect()->route('buildings.index')->with('status', 'Building updated.');
    }

    public function destroy(Building $building)
    {
        $building->delete();
        return redirect()->route('buildings.index')->with('status', 'Building deleted.');
    }
}
