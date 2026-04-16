<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\Unit;
use Illuminate\Http\Request;
use App\Traits\Auditable;

class UnitController extends Controller
{
    use Auditable;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $units = Unit::with('property')->get();
        return response()->json($units);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'property_id'  => 'required|exists:properties,id',
            'unit_number'  => 'required|string|max:100',
            'unit_type'    => 'required|string|max:100',
            'rent_amount'  => 'required|numeric|min:0',
            'status'       => 'in:vacant,occupied',
        ]);

        $unit = Unit::create([
            'property_id' => $request->property_id,
            'unit_number' => $request->unit_number,
            'unit_type'   => $request->unit_type,
            'rent_amount' => $request->rent_amount,
            'status'      => $request->status ?? 'vacant',
        ]);

        $this->audit(
            'UNIT_CREATED: property=' . $request->property_id .
            ', unit_number=' . $request->unit_number .
            ', type=' . $request->unit_type .
            ', rent=' . $request->rent_amount
        );

        return response()->json([
            'message' => 'Unit created successfully',
            'data'    => $unit
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $unit = Unit::with('property')->findOrFail($id);
        return response()->json($unit);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $unit = Unit::findOrFail($id);

        $old = $unit->only([
            'property_id',
            'unit_number',
            'unit_type',
            'rent_amount',
            'status'
        ]);

        $request->validate([
            'property_id'  => 'required|exists:properties,id',
            'unit_number'  => 'required|string|max:100',
            'unit_type'    => 'required|string|max:100',
            'rent_amount'  => 'required|numeric|min:0',
            'status'       => 'required|in:vacant,occupied',
        ]);

        $unit->update([
            'property_id' => $request->property_id,
            'unit_number' => $request->unit_number,
            'unit_type'   => $request->unit_type,
            'rent_amount' => $request->rent_amount,
            'status'      => $request->status,
        ]);

        $this->audit(
            'UNIT_UPDATED: id=' . $unit->id .
            ', old=' . json_encode($old) .
            ', new=' . json_encode($unit->only([
                'property_id',
                'unit_number',
                'unit_type',
                'rent_amount',
                'status'
            ]))
        );

        return response()->json([
            'message' => 'Unit updated successfully',
            'data'    => $unit
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $unit = Unit::findOrFail($id);

        $this->audit(
            'UNIT_DELETED: id=' . $unit->id .
            ', property_id=' . $unit->property_id .
            ', unit_number=' . $unit->unit_number .
            ', type=' . $unit->unit_type .
            ', rent=' . $unit->rent_amount .
            ', status=' . $unit->status
        );

        $unit->delete();

        return response()->json([
            'message' => 'Unit deleted successfully'
        ]);
    }

    /**
     * Get units by property
     */
    public function unitsByProperty($propertyId)
    {
        $units = Unit::where('property_id', $propertyId)->get();
        return response()->json($units);
    }

    public function storeUnit(Request $request, Property $property)
    {
        $unit = $property->units()->create([
            'unit_number' => $request->unit_number,
            'unit_type'   => $request->unit_type,
            'rent_amount' => $request->rent_amount,
            'status'      => 'vacant'
        ]);

        $this->audit(
            'UNIT_CREATED_VIA_PROPERTY: property=' . $property->id .
            ', unit=' . $unit->unit_number .
            ', type=' . $unit->unit_type
        );

        return response()->json($unit, 201);
    }

    public function vacant()
    {
        $units = Unit::where('status', 'vacant')->with('property')->get();

        $this->audit(
            'VACANT_UNITS_VIEWED: count=' . $units->count()
        );

        return response()->json($units);
    }
}