<?php

namespace App\Http\Controllers\API;

use App\Enums\UserRoles;
use App\Enums\UserStatus;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserVerification;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Enum;

class AuthController extends Controller
{

    public function me(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return ApiResponse::error('Unauthenticated', 401);
        }
        return ApiResponse::success(
            $user,
            'User retrieved successfully'
        );
    }

    public function login(Request $request)
    {
        $fields = $request->validate([
            'phone' => 'required|string',
            'password' => 'required|string|min:8',
        ]);

        $user = User::where('phone', $fields['phone'])->first();

        if (!$user || !Hash::check($fields['password'], $user->password)) {
            return ApiResponse::error('invalid_credentials', 401);
        }

        // Create Access Token
        $accessTokenPayload = [
            'sub'    => $user->id,
            'role'   => $user->role,
            'status' => $user->status,
            'exp'    => time() + 60 * 15, // 15 min
        ];
        $accessToken = JWT::encode($accessTokenPayload, env('JWT_SECRET'), 'HS256');

        // Create Refresh Token
        $refreshTokenPayload = [
            'sub' => $user->id,
            'exp' => time() + 60 * 60 * 24 * 7, // 7 days
        ];
        $refreshToken = JWT::encode($refreshTokenPayload, env('JWT_SECRET'), 'HS256');

        // Optionally store refresh token in DB to allow revocation
        $user->refresh_token = $refreshToken;
        $user->save();


        $cookie_access_token = cookie(
            'access_token',
            $accessToken,
            15,
            '/',       // path
            '.tlabli.vercel.app',      // domain must be null for localhost
            true,      // secure
            true,      // httpOnly
            false,
            'Lax'
        );
        $cookie_refresh_token = cookie(
            'refresh_token',
            $refreshToken,
            60 * 24 * 7,
            '/',       // path
            '.tlabli.vercel.app',      // domain must be null for localhost
            true,      // secure
            true,      // httpOnly
            false,
            'Lax'
        );
        return ApiResponse::success(['status' => $user->status], 'Logged in successfully', 200)->withCookie($cookie_access_token)->withCookie($cookie_refresh_token);
    }

    public function logout(Request $request)
    {
        $user = $request->user();

        if ($user) {
            // Optional: revoke refresh token in DB
            $user->refresh_token = null;
            $user->save();
        }
        // Revoke the token that was used to authenticate the current request
        $cookie_access_token = Cookie::forget(
            'access_token',
            -1,
            15,
            '/',       // path
            null,      // domain must be null for localhost
            true,      // secure
            true,      // httpOnly
            false,
            'Lax'
        );
        $cookie_refresh_token = Cookie::forget(
            'refresh_token',
            -1,
            60 * 24 * 7,
            '/',       // path
            null,      // domain must be null for localhost
            true,      // secure
            true,      // httpOnly
            false,
            'Lax'
        );

        return ApiResponse::success(null, 'Logged out successfully', 200)->withCookie($cookie_access_token)->withCookie($cookie_refresh_token);
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

        // Generate phone verification code
        $code = rand(100000, 999999);

        $user->verifications()->create([
            'type' => UserVerification::TYPE_PHONE,
            'code' => $code,
            'expires_at' => now()->addMinutes(5),
        ]);



        return ApiResponse::success(['user' => $user], 'User created successfully', 201);
    }
    public function registerArtisan(Request $request)
    {
        $userData = ApiResponse::validate($request->all(), [
            'full_name'     => 'required|string|max:255',
            'email' => 'nullable|email|unique:users,email',
            'phone' => 'required|string|unique:users,phone|max:15',
            'password' => 'required|min:8',
            'city' => 'required|string',
        ]);

        $artisanData = ApiResponse::validate($request->all(), [
            'profession' => 'required|string',
            'skills' => 'required|array'
        ]);



        $userData['role'] = UserRoles::Artisan->value;
        $userData['password'] = Hash::make($userData['password']);
        $user = User::create($userData);

        // create the artisan
        $user->artisan()->create($artisanData);

        // Generate phone verification code
        $code = rand(100000, 999999);

        $user->verifications()->create([
            'type' => UserVerification::TYPE_PHONE,
            'code' => $code,
            'expires_at' => now()->addMinutes(5),
        ]);

        return ApiResponse::success(['user' => $user], 'User created successfully', 201);
    }

    public function verifyAccount(Request $request)
    {
        $data = ApiResponse::validate($request->all(), [
            'code'     => 'required|string|max:6',
        ]);

        $user = $request->user();

        if ($user->isVerfied()) {
            return ApiResponse::error('already verified', 401);
        }
        $status = UserStatus::Active->value;

        if ($data['code'] == '123456') {
            if ($user->role == UserRoles::Artisan->value) {
                $status = UserStatus::ProfileIncomplete->value;
            }
            $user->status = $status;
            $user->verified_at = now();
            $user->is_verified = true;
            $user->save();
        } else {
            return ApiResponse::error('code incorrect', 401);
        }

        // Create Access Token
        $accessTokenPayload = [
            'sub'    => $user->id,
            'role'   => $user->role,
            'status' => $user->status,
            'exp'    => time() + 60 * 15, // 15 min
        ];
        $accessToken = JWT::encode($accessTokenPayload, env('JWT_SECRET'), 'HS256');
        $accessTokenCookie = cookie(
            'access_token',
            $accessToken, // e.g., 'active', 'suspended', 'not_verified'
            60 * 24 * 7,
            '/',
            null,
            true,
            true,
            false,
            'Lax'
        );

        return ApiResponse::success(['status' => $status], 'Account verified')->withCookie($accessTokenCookie);
    }

    public function refresh(Request $request)
    {
        $refreshToken = $request->cookie('refresh_token');
        
        if (!$refreshToken) {
            return ApiResponse::error('No refresh token', 401);
        }

        try {
            $payload = JWT::decode($refreshToken, new Key(env('JWT_SECRET'), 'HS256'));
            $user = User::find($payload->sub);
            if (!$user || $user->refresh_token !== $refreshToken) {
                return ApiResponse::error('Invalid refresh token', 401);
            }

            // Issue new access token
            $accessTokenPayload = [
                'sub'    => $user->id,
                'role'   => $user->role,
                'status' => $user->status,
                'exp'    => time() + 60 * 15,
            ];
            $accessToken = JWT::encode($accessTokenPayload, env('JWT_SECRET'), 'HS256');
            $access_token = cookie(
                'access_token',
                $accessToken,
                60 * 24 * 7,
                '/',       // path
                null,      // domain must be null for localhost
                true,      // secure
                true,      // httpOnly
                false,
                'Lax'
            );

            return ApiResponse::success(null, 'Token refreshed')->withCookie($access_token);
        } catch (\Exception $e) {
            return ApiResponse::error('Invalid token', 401);
        }
    }
}
