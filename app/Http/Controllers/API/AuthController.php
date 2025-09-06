<?php

namespace App\Http\Controllers\API;

use App\Enums\UserRoles;
use App\Enums\UserStatus;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserVerification;
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

        $token = $user->createToken('apptoken');
        $token->accessToken->expires_at = now()->addDays(7); // 7 days expiry
        $token->accessToken->save();


        $plainTextToken = $token->plainTextToken;
        $cookie = cookie(
            'token',
            $plainTextToken,
            60 * 24 * 7,
            '/',       // path
            null,      // domain must be null for localhost
            true,      // secure
            true,      // httpOnly
            false,
            'Lax'
        );
        // Status cookie (encrypted automatically by Laravel)
        $statusCookie = cookie(
            'user_status',
            $user->status->value, // e.g., 'active', 'suspended', 'not_verified'
            60 * 24 * 7,
            '/',
            null,
            true,
            true,
            false,
            'Lax'
        );
        return ApiResponse::success(['status' => $user->status], 'Logged in successfully', 200)->withCookie($cookie)->withCookie($statusCookie);
    }

    public function logout(Request $request)
    {
        // Revoke the token that was used to authenticate the current request
        $request->user()->currentAccessToken()->delete();
        $cookie = Cookie::forget('toeken');

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

        // Generate phone verification code
        $code = rand(100000, 999999);

        $user->verifications()->create([
            'type' => UserVerification::TYPE_PHONE,
            'code' => $code,
            'expires_at' => now()->addMinutes(5),
        ]);

        // Send SMS via Twilio

        $token = $user->createToken('apptoken');
        $token->accessToken->expires_at = now()->addDays(7); // 7 days expiry
        $token->accessToken->save();

        $plainTextToken = $token->plainTextToken;
        $cookie = cookie(
            'token',
            $plainTextToken,
            60 * 24 * 7,
            '/',       // path
            null,      // domain must be null for localhost
            true,      // secure
            true,      // httpOnly
            false,
            'Lax'
        );
        // Status cookie (encrypted automatically by Laravel)
        $statusCookie = cookie(
            'user_status',
            UserStatus::NotVerified->value, // e.g., 'active', 'suspended', 'not_verified'
            60 * 24 * 7,
            '/',
            null,
            true,
            true,
            false,
            'Lax'
        );

        return ApiResponse::success(['user' => $user], 'User created successfully', 201)->withCookie($cookie)->withCookie($statusCookie);
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
        $artisan = $user->artisan()->create($artisanData);

        // Generate phone verification code
        $code = rand(100000, 999999);

        $user->verifications()->create([
            'type' => UserVerification::TYPE_PHONE,
            'code' => $code,
            'expires_at' => now()->addMinutes(5),
        ]);

        // Send SMS via Twilio

        $token = $user->createToken('apptoken');
        $token->accessToken->expires_at = now()->addDays(7); // 7 days expiry
        $token->accessToken->save();

        $plainTextToken = $token->plainTextToken;
        $cookie = cookie(
            'token',
            $plainTextToken,
            60 * 24 * 7,
            '/',       // path
            null,      // domain must be null for localhost
            true,      // secure
            true,      // httpOnly
            false,
            'Lax'
        );
        // Status cookie (encrypted automatically by Laravel)
        $statusCookie = cookie(
            'user_status',
            UserStatus::NotVerified->value, // e.g., 'active', 'suspended', 'not_verified'
            60 * 24 * 7,
            '/',
            null,
            true,
            true,
            false,
            'Lax'
        );

        return ApiResponse::success(['user' => $user], 'User created successfully', 201)->withCookie($cookie)->withCookie($statusCookie);
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

        $statusCookie = cookie(
            'user_status',
            $status, // e.g., 'active', 'suspended', 'not_verified'
            60 * 24 * 7,
            '/',
            null,
            true,
            true,
            false,
            'lax'
        );

        return ApiResponse::success(['status' => $status], 'Account verified')->withCookie($statusCookie);
    }
}
