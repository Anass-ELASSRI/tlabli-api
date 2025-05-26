<?php

namespace App\Http\Controllers\API;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Craftman;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $fields = $request->validate([
            'email' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $fields['email'])->first();

        if (!$user || !Hash::check($fields['password'], $user->password)) {
            return response(['message' => 'Invalid credentials'], 401);
        }

        $token = $user->createToken('apptoken')->plainTextToken;

        return response()->json(['user' => $user, 'token' => $token], 200);
    }

    public function logout(Request $request)
    {
        // Revoke the token that was used to authenticate the current request
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully',
        ]);
    }
    public function register(Request $request)
    {
        $validator = ApiResponse::validate($request->all(), [
            'name'     => 'required|string|max:255',
            'email' => 'nullable|email|unique:users,email|required_without:phone',
            'phone' => 'nullable|string|unique:users,phone|max:15|required_without:email',
            'password' => 'required|min:8',
            'role' => 'required|in:' . User::ROLE_CRAFTMAN . ',' . User::ROLE_USER . ',',
        ]);


        $user = User::create([
            'name'         => $request->name,
            'email'        => $request->email,
            'phone'        => $request->phone,
            'password'     => Hash::make($request->password),
            'status'       => User::STATUS_PENDING,
            'role'         => $request->role,
        ]);
        return response()->json([
            'message'       => 'User created successfully.',
            'user'          => $user,
            'next_step'     => $user->role == User::ROLE_CRAFTMAN ? Craftman::STEP_BASIC_INFO : null,
        ], 201);
    }
}
