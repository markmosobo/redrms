<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\User;
use Illuminate\Http\Request;
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
        $properties = Property::with('landlord')->get();
        $landlords = User::where('role', 'landlord')->get();

        return response()->json([
            'properties' => $properties,
            'landlords' => $landlords,
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

        // 🔔 NOTIFICATIONS

        // Notify landlord
        $this->notifyUser(
            $property->landlord_id,
            'Property Created',
            'Your property "' . $property->property_name . '" has been successfully registered.',
            'property_created',
            $property->id,
            'property'
        );

        // Notify managers
        $this->notifyRoles(
            ['manager'],
            'New Property Added',
            'A new property "' . $property->property_name . '" has been added in the system.',
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
     * UPDATE PROPERTY
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

        // 🔔 NOTIFICATIONS

        $this->notifyUser(
            $property->landlord_id,
            'Property Updated',
            'Your property "' . $property->property_name . '" has been updated.',
            'property_updated',
            $property->id,
            'property'
        );

        $this->notifyRoles(
            ['manager'],
            'Property Updated',
            'Property "' . $property->property_name . '" has been updated.',
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
     * DELETE PROPERTY
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

        // 🔔 NOTIFICATIONS BEFORE DELETE

        $this->notifyUser(
            $property->landlord_id,
            'Property Deleted',
            'Your property "' . $property->property_name . '" has been removed from the system.',
            'property_deleted',
            $property->id,
            'property'
        );

        $this->notifyRoles(
            ['manager'],
            'Property Removed',
            'A property has been deleted from the system.',
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
     * LANDLORD PROPERTIES
     */
    public function landlordProperties(User $landlord)
    {
        return $landlord->properties()
            ->withCount('units')
            ->latest()
            ->get();
    }

    /**
     * MANAGER PROPERTIES
     */
    public function managerProperties($managerId)
    {
        return Property::where('manager_id', $managerId)
            ->withCount('units')
            ->latest()
            ->get();
    }

    /**
     * CREATE PROPERTY FOR LANDLORD
     */
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

        $this->notifyUser(
            $landlord->id,
            'Property Created',
            'A new property "' . $property->property_name . '" has been created for you.',
            'property_created',
            $property->id,
            'property'
        );

        return response()->json($property, 201);
    }

    /**
     * ASSIGN TO MANAGER
     */
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

        // 🔔 NOTIFICATIONS

        $this->notifyUser(
            $managerId,
            'Property Assignment',
            'You have been assigned new property(ies).',
            'property_assigned',
            null,
            'property'
        );

        $this->notifyRoles(
            ['landlord'],
            'Property Manager Assigned',
            'A manager has been assigned to property(ies).',
            'property_assigned',
            null,
            'property'
        );

        return response()->json([
            'message' => 'Assigned successfully'
        ]);
    }
}