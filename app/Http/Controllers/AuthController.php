<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Traits\Auditable;
use App\Traits\NotifiesUsers;

class AuthController extends Controller
{
    use Auditable, NotifiesUsers;

    /**
     * REGISTER
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:100',
            'last_name'  => 'required|string|max:100',
            'email'      => 'required|string|email|max:100|unique:users',
            'password'   => 'required|string|min:6',
            'role'       => 'nullable|in:tenant,landlord,manager,admin'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::create([
            'full_name' => $request->first_name . ' ' . $request->last_name,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
            'role'      => $request->role ?? 'tenant',
            'status'    => 1,
        ]);

        $token = auth('api')->login($user);

        // 🔥 AUDIT
        $this->audit(
            'USER_REGISTERED: ' . $user->full_name,
            $user->id
        );

        // 🔔 NOTIFICATIONS (ONLY IMPORTANT EVENT)

        // If tenant registers → notify managers/admins
        if ($user->role === 'tenant') {
            $this->notifyRoles(
                ['manager', 'admin'],
                'New Tenant Registered',
                $user->full_name . ' has registered as a tenant.',
                'user_registered',
                $user->id,
                'user'
            );
        }

        // If landlord registers → notify admin only
        if ($user->role === 'landlord') {
            $this->notifyRoles(
                ['admin'],
                'New Landlord Registered',
                $user->full_name . ' has registered as a landlord.',
                'user_registered',
                $user->id,
                'user'
            );
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'User registered successfully.',
            'user'    => $user,
            'token'   => $token,
        ], 201);
    }

    /**
     * LOGIN
     */
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (!$token = auth('api')->attempt($credentials)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        $user = auth('api')->user();

        $this->audit(
            'USER_LOGIN: ' . ($user->full_name ?? 'unknown'),
            $user->id
        );

        // ❌ NO NOTIFICATION (intentional)

        return response()->json([
            'status'      => 'success',
            'user'        => $user,
            'token'       => $token,
            'token_type'  => 'bearer',
            'expires_in'  => auth('api')->factory()->getTTL() * 60
        ]);
    }

    /**
     * LOGOUT
     */
    public function logout()
    {
        try {
            $user = auth('api')->user();

            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthenticated'
                ], 401);
            }

            auth('api')->logout();

            $this->audit(
                'USER_LOGOUT: ' . $user->full_name,
                $user->id
            );

            // ❌ NO NOTIFICATION

            return response()->json([
                'status' => 'success',
                'message' => 'User logged out successfully.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Logout failed'
            ], 500);
        }
    }

    /**
     * CURRENT USER
     */
    public function me()
    {
        return response()->json([
            'status' => 'success',
            'user' => auth('api')->user()
        ]);
    }

    /**
     * REFRESH TOKEN
     */
    public function refresh()
    {
        return response()->json([
            'status'      => 'success',
            'token'       => auth('api')->refresh(),
            'token_type'  => 'bearer',
            'expires_in'  => auth('api')->factory()->getTTL() * 60
        ]);
    }
}