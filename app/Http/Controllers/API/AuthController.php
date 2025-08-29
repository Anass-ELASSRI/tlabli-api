<?php

namespace App\Http\Controllers\API;

use App\Enums\UserRoles;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Enum;

class AuthController extends Controller
{

    public function test(Request $request)
    {
        return ApiResponse::success([
            'cookies' => $request->cookies->all(),
            'headers' => $request->headers->all(),
        ], 'test');
    }
    public function me(Request $request)
    {
        return ApiResponse::success([
            'cookies' => $request->cookies->all(),
            'headers' => $request->headers->all(),
        ], 'test');
        $user = $request->user();
        if (!$user) {
            return ApiResponse::error('Unauthenticated', 401);
        }
        return ApiResponse::success(
            $request->user(),
            'User retrieved successfully'
        );
    }

    public function login(Request $request)
    {
        $fields = $request->validate([
            'phone' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('phone', $fields['phone'])->first();

        if (!$user || !Hash::check($fields['password'], $user->password)) {
            return ApiResponse::error('invalid_credentials', 401);
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
            '',             // domain (null means current domain)
            true,             // secure (only sent over HTTPS) => to true in production
            true,             // HttpOnly (JS can't access)
            false,            // raw
            'None'          // SameSite
        );
        return ApiResponse::success($user, 'Login successful', 200)->withCookie($cookie);
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
            'full_name'     => 'required|string|max:255',
            'email' => 'nullable|email|unique:users,email',
            'phone' => 'required|string|unique:users,phone|max:15',
            'password' => 'required|min:8',
            'city' => 'required|string',
            'role' => ['required', new Enum(UserRoles::class)],
        ]);


        $data['password'] = Hash::make($data['password']);
        $user = User::create($data);

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
        return ApiResponse::success($user, 'User created successfully', 201)->withCookie($cookie);
    }
}
