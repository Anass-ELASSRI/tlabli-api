<?php

namespace App\Http\Controllers\accountVerificatiom;

use App\Enums\UserRoles;
use App\Enums\UserStatus;
use App\Helpers\ApiResponse;
use App\Helpers\CookiesHelper;
use App\Helpers\JWTHelper;
use App\Http\Controllers\Controller;
use App\Models\UserVerification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PhoneVerificationController extends Controller
{

    public function verify(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $user = $request->user();
        $verification = $user->verifications()
            ->where('type', UserVerification::TYPE_PHONE)
            ->latest()
            ->first();

        if (!$verification) {
            return ApiResponse::error('No verification code found', 404);
        }

        // Max attempts check
        $waitMinutes = 5;
        if ($verification->attempts >= 5) {
            $minutesSinceLast = now()->diffInMinutes($verification->last_attempt_at);
            if ($minutesSinceLast < $waitMinutes) {
                return ApiResponse::error(
                    "Too many attempts. Wait " . ($waitMinutes - $minutesSinceLast) . " minutes.",
                    429
                );
            } else {
                $verification->update(['attempts' => 0]);
            }
        }

        // Expiration check
        if ($verification->expires_at->isPast()) {
            return ApiResponse::error('Code expired. Please request a new one.', 422);
        }

        // Code check
        if ($verification->code !== $request->code) {
            $verification->update([
                'attempts' => $verification->attempts + 1,
                'last_attempt_at' => now(),
            ]);
            return ApiResponse::error('Invalid code', 422);
        }

        // Success: wrap in transaction for atomicity
        DB::transaction(function () use ($user, $verification) {
            $user->update(['phone_verified_at' => now()]);
            $verification->delete();
        });
        $status = UserStatus::Active->value;
        if ($user->role == UserRoles::Artisan) $status = UserStatus::ProfileIncomplete->value;

        $user->update([
            'status' => $status,
            'is_verified' => true
        ]);
        $cookieHelper = new CookiesHelper();
        $JWThelper = new JWTHelper();

        $accessToken = $JWThelper->generateJwt($user, 60 * 15);
        $cookie_access_token = $cookieHelper->generateCookie(
            'access_token',
            $accessToken,
            15
        );

        return ApiResponse::success(['status' => $status], 'Phone verified successfully')->withCookie($cookie_access_token);
    }

    public function resend(Request $request)
    {
        $user = $request->user();

        // 1️⃣ Throttle sending (1 min)
        $latest = $user->verifications()
            ->where('type', UserVerification::TYPE_PHONE)
            ->latest()
            ->first();

        if ($latest && now()->diffInSeconds($latest->created_at) < 60) {
            $wait = 60 - now()->diffInSeconds($latest->created_at);
            return ApiResponse::error("Please wait {$wait} seconds before requesting a new code", 429);
        }

        // 2️⃣ Generate 6-digit code
        $code = rand(100000, 999999);

        // 3️⃣ Store code in DB
        DB::transaction(function () use ($user, $code) {
            $user->verifications()->create([
                'type' => UserVerification::TYPE_PHONE,
                'code' => $code,
                'expires_at' => now()->addMinutes(5),
                'attempts' => 0,
            ]);
        });

        return ApiResponse::success(null, 'Verification code resent successfully');
    }
}
