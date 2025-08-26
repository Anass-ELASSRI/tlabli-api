<?php

namespace App\Http\Controllers\API;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Artisan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{

    public function user(Request $request)
    {
        return ApiResponse::success(
            $request->user(),
            'User retrieved successfully',
            200
        );
    }

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

        $token = $user->createToken('apptoken');
        $token->accessToken->expires_at = now()->addDays(7); // 7 days expiry
        $token->accessToken->save();

        $plainTextToken = $token->plainTextToken;
        $cookie = cookie(
            'jwt',            // name
            $plainTextToken,           // value
            60 * 24 * 7,      // minutes (7 days)
            '/',              // path
            null,             // domain (null means current domain)
            true,             // secure (only sent over HTTPS)
            true,             // HttpOnly (JS can't access)
            false,            // raw
            'Strict'          // SameSite
        );
        return ApiResponse::success([
            'user' => $user,
        ], 'Login successful', 200)->withCookie($cookie);
    }

    public function logout(Request $request)
    {
        // Revoke the token that was used to authenticate the current request
        $request->user()->currentAccessToken()->delete();
        $cookie = Cookie::forget('jwt');

        return ApiResponse::success(null, 'Logged out successfully', 200)->withCookie($cookie);
    }
    public function register(Request $request)
    {
        $data = ApiResponse::validate($request->all(), [
            'first_name'     => 'required|string|max:255',
            'last_name'     => 'required|string|max:255',
            'email' => 'nullable|email|unique:users,email|required_without:phone',
            'phone' => 'nullable|string|unique:users,phone|max:15|required_without:email',
            'password' => 'required|min:8',
            'role' => 'required|in:' . User::ROLE_CRAFTMAN . ',' . User::ROLE_USER . ',',
        ]);


        $data['password'] = Hash::make($data['password']);
        $user = User::create($data);
        return response()->json([
            'message'       => 'User created successfully.',
            'user'          => $user,
            'next_step'     => $user->role == User::ROLE_CRAFTMAN ? Artisan::STEP_BASIC_INFO : null,
        ], 201);
    }
}
