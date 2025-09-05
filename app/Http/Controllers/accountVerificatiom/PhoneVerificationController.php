<?php

namespace App\Http\Controllers\accountVerificatiom;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\UserVerification;
use Illuminate\Http\Request;
use Twilio\Rest\Client;

class PhoneVerificationController extends Controller
{
    public function verify(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $user = $request()->user();
        $verification = $user->verifications()
            ->where('type', UserVerification::TYPE_PHONE)
            ->latest()
            ->first();

        if (!$verification) {
            return ApiResponse::error('No verification code found', 404);
        }

        // Check if max attempts exceeded
        if ($verification->attempts >= 5) {
            $wait = 5; // minutes
            $diff = now()->diffInMinutes($verification->last_attempt_at);

            if ($diff < $wait) {
                return ApiResponse::error("Too many attempts. Wait " . ($wait - $diff) . " minutes.", 429);
            } else {
                $verification->attempts = 0;
                $verification->save();
            }
        }

        // Check expiration
        if ($verification->expires_at->isPast()) {
            return ApiResponse::error('Code expired. Please request a new one.', 422);
        }

        // Check code
        if ($verification->code !== $request->code) {
            $verification->attempts += 1;
            $verification->last_attempt_at = now();
            $verification->save();
            return ApiResponse::error('Invalid code', 422);
        }

        // Success
        $user->phone_verified_at = now();
        $user->save();

        $verification->delete(); // remove used code
        return ApiResponse::success(null, 'Phone verified successfully');
    }

    // Optional: Resend code
    public function resend(Request $request)
    {
        $user = $request()->user();

        // Optional: throttle sending SMS
        $latest = $user->verifications()->where('type', 'phone')->latest()->first();
        if ($latest && now()->diffInMinutes($latest->created_at) < 1) {
            return ApiResponse::error('Please wait before requesting a new code', 429);
        }

        $code = rand(100000, 999999);
        $user->verifications()->create([
            'type' => UserVerification::TYPE_PHONE,
            'code' => $code,
            'expires_at' => now()->addMinutes(5),
        ]);

        // Send SMS

        return ApiResponse::success(null, 'Verification code resent successfully');
    }
}
