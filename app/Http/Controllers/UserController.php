<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Traits\Auditable;

class UserController extends Controller
{
    use Auditable;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::get();

        $this->audit(
            'USERS_VIEWED: count=' . $users->count()
        );

        return response()->json($users);
    }

    public function landlords()
    {
        $users = User::where('role', 'landlord')->get();

        $this->audit(
            'LANDLORDS_VIEWED: count=' . $users->count()
        );

        return response()->json($users);
    }

    public function managers()
    {
        $users = User::where('role', 'manager')
            ->withCount('managedProperties')
            ->get();

        $this->audit(
            'MANAGERS_VIEWED: count=' . $users->count()
        );

        return response()->json($users);
    }

    public function tenants()
    {
        $users = User::where('role', 'tenant')
            ->with('activeTenancy.unit.property')
            ->get();

        $this->audit(
            'TENANTS_VIEWED: count=' . $users->count()
        );

        return response()->json($users);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email',
            'phone'     => 'nullable|string|max:20',
            'role'      => 'required|string',
            'status'    => 'required|string',
            'password'  => 'required|min:6',
        ]);

        $user = new User();
        $user->full_name = $request->full_name;
        $user->email     = $request->email;
        $user->phone     = $request->phone;
        $user->role      = $request->role;
        $user->status    = $request->status;
        $user->password  = Hash::make($request->password);
        $user->save();

        $this->audit(
            'USER_CREATED: id=' . $user->id .
            ', name=' . $user->full_name .
            ', role=' . $user->role .
            ', email=' . $user->email
        );

        return response()->json([
            'message' => 'User created successfully',
            'user' => $user
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = User::findOrFail($id);

        $this->audit(
            'USER_VIEWED: id=' . $user->id .
            ', role=' . $user->role
        );

        return response()->json($user);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = User::findOrFail($id);

        $old = $user->only([
            'full_name',
            'email',
            'phone',
            'role',
            'status'
        ]);

        $request->validate([
            'full_name' => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email,' . $id,
            'phone'     => 'nullable|string|max:20',
            'role'      => 'required|string',
            'status'    => 'required|string',
            'password'  => 'nullable|min:6',
        ]);

        $user->full_name = $request->full_name;
        $user->email     = $request->email;
        $user->phone     = $request->phone;
        $user->role      = $request->role;
        $user->status    = $request->status;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        $this->audit(
            'USER_UPDATED: id=' . $user->id .
            ', old=' . json_encode($old) .
            ', new=' . json_encode($user->only([
                'full_name',
                'email',
                'phone',
                'role',
                'status'
            ]))
        );

        return response()->json([
            'message' => 'User updated successfully',
            'user' => $user
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::findOrFail($id);

        $this->audit(
            'USER_DELETED: id=' . $user->id .
            ', name=' . $user->full_name .
            ', role=' . $user->role .
            ', email=' . $user->email
        );

        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully'
        ]);
    }
}