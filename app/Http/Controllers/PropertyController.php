<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\User;
use Illuminate\Http\Request;

class PropertyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Fetch all properties with their related landlord
        $properties = Property::with('landlord')->get();

        // Fetch all landlords (assuming you have a Landlord model)
        $landlords = User::where('role','landlord')->get();  

        // Return both in a single JSON response
        return response()->json([
            'properties' => $properties,
            'landlords' => $landlords,
        ]);
    }

    /**
     * Store a newly created resource in storage.
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

        return response()->json([
            'message' => 'Property created successfully',
            'data'    => $property
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $property = Property::with('landlord')->findOrFail($id);
        return response()->json($property);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $property = Property::findOrFail($id);

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

        return response()->json([
            'message' => 'Property updated successfully',
            'data'    => $property
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $property = Property::findOrFail($id);
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
    
    public function storeProperty(Request $request, User $landlord)
    {
        $property = $landlord->properties()->create([
        'property_name' => $request->property_name,
        'location' => $request->location,
        'description' => $request->description
        ]);

        return response()->json($property, 201);
    }  
}