<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\SystemLog;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    /**
     * Register a new user and return JWT
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:100',
            'last_name'  => 'required|string|max:100',
            'email'      => 'required|string|email|max:100|unique:users',
            'password'   => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::create([
            'name'       => $request->first_name . ' ' . $request->last_name,
            'email'      => $request->email,
            'password'   => Hash::make($request->password),
            'role'       => $request->role ?? 'user',
            'status'     => 1,
        ]);


        $token = auth('api')->login($user);

        // ✅ Use the created user, NOT auth()->user()
        AuditLog::create([
            'user_id' => $user->id,
            'description' => $user->name . ' created account'
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'User registered successfully.',
            'user'    => $user,
            'token'   => $token,
        ], 201);
    }


    /**
     * Login user and return JWT
     */
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (!$token = auth('api')->attempt($credentials)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        //record system log
        AuditLog::create([
            'user_id' => auth('api')->user()->id,
            'description' => auth('api')->user()->name.' logged in'
        ]);        

        return response()->json([
            'status' => 'success',
            'user' => auth('api')->user(),
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60
        ]);
    }

    /**
     * Logout user (invalidate token)
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

            AuditLog::create([
                'user_id' => $user->id,
                'description' => $user->name . ' logged out'
            ]);

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
     * Get authenticated user
     */
    public function me()
    {
        return response()->json(['status' => 'success', 'user' => auth('api')->user()]);
    }

    /**
     * Refresh token
     */
    public function refresh()
    {
        return response()->json([
            'status' => 'success',
            'token' => auth('api')->refresh(),
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60
        ]);
    }
}
