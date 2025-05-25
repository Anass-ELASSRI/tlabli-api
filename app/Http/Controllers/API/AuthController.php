<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
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
    public function registerCraftsman(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|unique:users|max:15',
            'password' => 'required|string',

        ]);
        // Hash the password if it's provided
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }
        // Set the role based on the type
        $data['role'] = User::ROLE_CRAFTMAN;
        $craftman = User::create($data);
        return response()->json([
            'data' => $craftman,
            'message' => 'Craftman created successfully',
            'success' => true,
        ], 201);
    }
    public function registerClient(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|unique:users|max:15',
            'password' => 'required|string',

        ]);
        // Hash the password if it's provided
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }
        // Set the role based on the type
        $data['role'] = User::ROLE_USER;


        $craftman = User::create($data);

        return response()->json([
            'data' => $craftman,
            'message' => 'client created successfully',
            'success' => true,
        ], 201);
    }
}
