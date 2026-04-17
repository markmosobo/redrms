<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Traits\Auditable;
use App\Traits\NotifiesUsers;

class PropertyController extends Controller
{
    use Auditable, NotifiesUsers;

    /**
     * LIST
     */
    public function index()
    {
        return response()->json([
            'properties' => Property::with('landlord')->get(),
            'landlords'  => User::where('role', 'landlord')->get(),
        ]);
    }

    /**
     * CREATE PROPERTY
     */
    public function store(Request $request)
    {
        $request->validate([
            'landlord_id'   => 'required|exists:users,id',
            'property_name' => 'required|string|max:255',
            'location'      => 'required|string|max:255',
            'description'   => 'nullable|string',
        ]);

        $property = null;

        DB::transaction(function () use ($request, &$property) {

            $property = Property::create([
                'landlord_id'   => $request->landlord_id,
                'property_name' => $request->property_name,
                'location'      => $request->location,
                'description'   => $request->description,
            ]);

            $this->audit(
                "Created property: {$property->property_name} (ID: {$property->id})"
            );
        });

        $this->notifyUser(
            $property->landlord_id,
            'Property Created',
            "Your property '{$property->property_name}' has been successfully registered.",
            'property_created',
            $property->id,
            'property'
        );

        $this->notifyRoles(
            ['manager'],
            'New Property Added',
            "A new property '{$property->property_name}' has been added.",
            'property_created',
            $property->id,
            'property'
        );

        return response()->json([
            'message' => 'Property created successfully',
            'data'    => $property
        ], 201);
    }

    /**
     * SHOW
     */
    public function show(string $id)
    {
        return response()->json(
            Property::with('landlord')->findOrFail($id)
        );
    }

    /**
     * UPDATE
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

        $property->update($request->only([
            'landlord_id',
            'property_name',
            'location',
            'description'
        ]));

        $this->audit(
            "Updated property: {$property->property_name} (ID: {$property->id})"
        );

        $this->notifyUser(
            $property->landlord_id,
            'Property Updated',
            "Your property '{$property->property_name}' has been updated.",
            'property_updated',
            $property->id,
            'property'
        );

        return response()->json([
            'message' => 'Property updated successfully',
            'data'    => $property
        ]);
    }

    /**
     * DELETE
     */
    public function destroy(string $id)
    {
        $property = Property::findOrFail($id);

        $this->audit(
            "Deleted property: {$property->property_name} (ID: {$property->id})"
        );

        $this->notifyUser(
            $property->landlord_id,
            'Property Deleted',
            "Your property '{$property->property_name}' has been removed.",
            'property_deleted',
            $property->id,
            'property'
        );

        $property->delete();

        return response()->json([
            'message' => 'Property deleted successfully'
        ]);
    }

    /**
     * ASSIGN TO MANAGER
     */
    public function assignToManager(Request $request, $managerId)
    {
        $request->validate([
            'property_ids' => 'required|array'
        ]);

        DB::transaction(function () use ($request, $managerId) {

            Property::whereIn('id', $request->property_ids)
                ->update(['manager_id' => $managerId]);

            $this->audit(
                "Assigned properties (" . implode(',', $request->property_ids) . ") to manager ID: {$managerId}"
            );
        });

        $this->notifyUser(
            $managerId,
            'Property Assignment',
            'You have been assigned new property(ies).',
            'property_assigned',
            null,
            'property'
        );

        return response()->json([
            'message' => 'Assigned successfully'
        ]);
    }
}