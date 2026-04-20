<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use App\Models\UtilityReading;
use App\Models\UtilityType;
use Carbon\Carbon;
use Illuminate\Http\Request;

class UtilityController extends Controller
{
    public function index(Request $request)
    {
        $query = UtilityReading::with('unit.building', 'utilityType');

        if ($request->filled('utility_type_id')) {
            $query->where('utility_type_id', $request->utility_type_id);
        }
        if ($request->filled('month')) {
            $query->whereDate('period_month', Carbon::parse($request->month)->startOfMonth()->toDateString());
        }

        $readings = $query->latest('period_month')->paginate(20)->withQueryString();
        $types = UtilityType::orderBy('name')->get();

        return view('utilities.index', compact('readings', 'types'));
    }

    public function types()
    {
        $types = UtilityType::orderBy('name')->get();
        return view('utilities.types', compact('types'));
    }

    public function storeType(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'unit_label' => ['nullable', 'string', 'max:30'],
            'rate_per_unit' => ['required', 'numeric', 'min:0'],
            'flat_fee' => ['required', 'numeric', 'min:0'],
            'is_metered' => ['nullable', 'boolean'],
            'active' => ['nullable', 'boolean'],
        ]);
        $data['is_metered'] = $request->boolean('is_metered');
        $data['active'] = $request->boolean('active', true);
        UtilityType::create($data);
        return redirect()->route('utilities.types')->with('status', 'Utility type added.');
    }

    public function updateType(Request $request, UtilityType $utilityType)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'unit_label' => ['nullable', 'string', 'max:30'],
            'rate_per_unit' => ['required', 'numeric', 'min:0'],
            'flat_fee' => ['required', 'numeric', 'min:0'],
            'is_metered' => ['nullable', 'boolean'],
            'active' => ['nullable', 'boolean'],
        ]);
        $data['is_metered'] = $request->boolean('is_metered');
        $data['active'] = $request->boolean('active');
        $utilityType->update($data);
        return redirect()->route('utilities.types')->with('status', 'Utility type updated.');
    }

    public function destroyType(UtilityType $utilityType)
    {
        $utilityType->delete();
        return redirect()->route('utilities.types')->with('status', 'Utility type removed.');
    }

    public function createReading()
    {
        $units = Unit::with('building')->orderBy('building_id')->orderBy('unit_number')->get();
        $types = UtilityType::where('active', true)->orderBy('name')->get();
        return view('utilities.reading_create', compact('units', 'types'));
    }

    public function storeReading(Request $request)
    {
        $data = $request->validate([
            'unit_id' => ['required', 'exists:units,id'],
            'utility_type_id' => ['required', 'exists:utility_types,id'],
            'period_month' => ['required', 'date'],
            'previous_reading' => ['nullable', 'numeric', 'min:0'],
            'current_reading' => ['nullable', 'numeric', 'min:0'],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);

        $type = UtilityType::findOrFail($data['utility_type_id']);
        $data['period_month'] = Carbon::parse($data['period_month'])->startOfMonth()->toDateString();
        $data['previous_reading'] = $data['previous_reading'] ?? 0;
        $data['current_reading'] = $data['current_reading'] ?? 0;

        if ($type->is_metered) {
            $data['consumption'] = max(0, $data['current_reading'] - $data['previous_reading']);
            $data['amount'] = round($data['consumption'] * (float) $type->rate_per_unit, 2);
        } else {
            $data['consumption'] = 0;
            $data['amount'] = $data['amount'] ?? (float) $type->flat_fee;
        }

        UtilityReading::updateOrCreate(
            [
                'unit_id' => $data['unit_id'],
                'utility_type_id' => $data['utility_type_id'],
                'period_month' => $data['period_month'],
            ],
            $data
        );

        return redirect()->route('utilities.index')->with('status', 'Reading recorded.');
    }

    public function destroyReading(UtilityReading $reading)
    {
        $reading->delete();
        return back()->with('status', 'Reading deleted.');
    }
}
