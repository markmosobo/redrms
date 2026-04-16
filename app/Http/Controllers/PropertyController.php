<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\User;
use Illuminate\Http\Request;
use App\Traits\Auditable;

class PropertyController extends Controller
{
    use Auditable;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $properties = Property::with('landlord')->get();
        $landlords = User::where('role', 'landlord')->get();

        return response()->json([
            'properties' => $properties,
            'landlords' => $landlords,
        ]);
    }

    /**
     * Store a newly created resource.
     */
    public function store(Request $request)
    {
        $request->validate([
            'landlord_id'   => 'required|exists:users,id',
            'property_name' => 'required|string|max:255',
            'location'      => 'required|string|max:255',
            'description'   => 'nullable|string',
        ]);

        $property = Property::create([
            'landlord_id'   => $request->landlord_id,
            'property_name' => $request->property_name,
            'location'      => $request->location,
            'description'   => $request->description,
        ]);

        $this->audit(
            'PROPERTY_CREATED',
            json_encode([
                'property_id' => $property->id,
                'name'        => $property->property_name,
                'location'    => $property->location,
                'landlord_id' => $property->landlord_id,
            ])
        );

        return response()->json([
            'message' => 'Property created successfully',
            'data'    => $property
        ], 201);
    }

    /**
     * Show resource.
     */
    public function show(string $id)
    {
        $property = Property::with('landlord')->findOrFail($id);
        return response()->json($property);
    }

    /**
     * Update resource.
     */
    public function update(Request $request, string $id)
    {
        $property = Property::findOrFail($id);

        $old = $property->only([
            'landlord_id',
            'property_name',
            'location',
            'description'
        ]);

        $request->validate([
            'landlord_id'   => 'required|exists:users,id',
            'property_name' => 'required|string|max:255',
            'location'      => 'required|string|max:255',
            'description'   => 'nullable|string',
        ]);

        $property->update([
            'landlord_id'   => $request->landlord_id,
            'property_name' => $request->property_name,
            'location'      => $request->location,
            'description'   => $request->description,
        ]);

        $this->audit(
            'PROPERTY_UPDATED',
            json_encode([
                'property_id' => $property->id,
                'old' => $old,
                'new' => $property->only([
                    'landlord_id',
                    'property_name',
                    'location',
                    'description'
                ])
            ])
        );

        return response()->json([
            'message' => 'Property updated successfully',
            'data'    => $property
        ]);
    }

    /**
     * Delete resource.
     */
    public function destroy(string $id)
    {
        $property = Property::findOrFail($id);

        $this->audit(
            'PROPERTY_DELETED',
            json_encode([
                'property_id'   => $property->id,
                'property_name' => $property->property_name,
                'location'      => $property->location,
                'landlord_id'   => $property->landlord_id,
            ])
        );

        $property->delete();

        return response()->json([
            'message' => 'Property deleted successfully'
        ]);
    }

    public function landlordProperties(User $landlord)
    {
        return $landlord->properties()
            ->withCount('units')
            ->latest()
            ->get();
    }

    public function managerProperties($managerId)
    {
        return Property::where('manager_id', $managerId)
            ->withCount('units')
            ->latest()
            ->get();
    }

    public function storeProperty(Request $request, User $landlord)
    {
        $property = $landlord->properties()->create([
            'property_name' => $request->property_name,
            'location'      => $request->location,
            'description'   => $request->description
        ]);

        $this->audit(
            'PROPERTY_CREATED',
            json_encode([
                'property_id' => $property->id,
                'name' => $property->property_name,
                'landlord_id' => $landlord->id
            ])
        );

        return response()->json($property, 201);
    }

    public function assignToManager(Request $request, $managerId)
    {
        $request->validate([
            'property_ids' => 'required|array'
        ]);

        Property::whereIn('id', $request->property_ids)
            ->update([
                'manager_id' => $managerId
            ]);

        $this->audit(
            'PROPERTY_ASSIGNED_TO_MANAGER',
            json_encode([
                'manager_id' => $managerId,
                'property_ids' => $request->property_ids
            ])
        );

        return response()->json(['message' => 'Assigned successfully']);
    }
}