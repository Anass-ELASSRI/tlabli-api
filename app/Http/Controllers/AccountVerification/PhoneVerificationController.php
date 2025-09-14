<?php

namespace App\Http\Controllers\AccountVerification;

use App\Enums\UserRoles;
use App\Enums\UserStatus;
use App\Helpers\ApiResponse;
use App\Helpers\CookiesHelper;
use App\Helpers\JWTHelper;
use App\Http\Controllers\Controller;
use App\Models\UserVerification;
use App\Services\UserVerificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PhoneVerificationController extends Controller
{


    protected UserVerificationService $verificationService;

    public function __construct(UserVerificationService $verificationService)
    {
        $this->verificationService = $verificationService;
    }
    
    public function status(Request $request)
    {
        [$resendSeconds, $canVerify, $message] = $this->getVerificationStatus($request->user());
        return ApiResponse::success([
            'resend_seconds' => $resendSeconds,
            'can_verify' => $canVerify,
            'message' => $message,
        ]);
    }

    public function verify(Request $request)
    {
        $request->validate(['code' => 'required|string']);
        $user = $request->user();
        $verification = $this->latestPhoneVerification($user);

        if (!$verification) {
            return ApiResponse::error(__('auth.verification_code_not_found'), 404, ['can_verify' => false]);
        }

        [$resendSeconds, $canVerify, $message] = $this->getVerificationStatus($verification);

        if (!$canVerify) {
            return ApiResponse::error($message ?? __('auth.too_many_attempts_request_new'), 429, ['can_verify' => false]);
        }

        if ($verification->code !== $request->code) {
            $verification->update(['attempts' => $verification->attempts + 1, 'last_attempt_at' => now()]);
            $message = __('auth.verification_code_invalid');
            if ($verification->attempts >= UserVerification::MaxAttempts) {
                $message = __('auth.too_many_attempts_request_new');
            }
            return ApiResponse::error($message, 409, [
                'can_verify' => $verification->attempts < UserVerification::MaxAttempts
            ]);
        }

        $status = $user->role === UserRoles::Artisan
            ? UserStatus::ProfileIncomplete->value
            : UserStatus::Active->value;

        DB::transaction(function () use ($user, $verification, $status) {
            $user->update(['phone_verified_at' => now(), 'status' => $status]);
            $verification->delete();
        });

        $cookie = (new CookiesHelper())->generateCookie(
            'access_token',
            (new JWTHelper())->generateJwt($user, 300),
            60 * 24 * 7
        );

        return ApiResponse::success(['status' => $status], __('auth.phone_verified_successfully'))->withCookie($cookie);
    }

    public function resend(Request $request)
    {
        $user = $request->user();
        $latest = $this->latestPhoneVerification($user);

        if ($latest) {
            [$resendSeconds, $canVerify] = $this->getVerificationStatus($latest);
            if ($resendSeconds > 0) {
                return ApiResponse::error(
                    $this->humanReadableWait($resendSeconds),
                    429,
                    ['resend_seconds' => $resendSeconds, 'can_verify' => $canVerify]
                );
            }
        }

        $sendCount = $latest?->normalizedSendCount() ?? 0;
        $code = random_int(100000, 999999);

        DB::transaction(function () use ($user, $code, $sendCount) {
            $user->verifications()->create([
                'type' => UserVerification::TYPE_PHONE,
                'code' => $code,
                'expires_at' => now()->addMinutes(5),
                'attempts' => 0,
                'send_count' => $sendCount + 1,
            ]);
        });

        $waitSeconds = UserVerification::waitTimeForSendCount($sendCount + 1);

        return ApiResponse::success(['resend_seconds' => $waitSeconds], __('auth.verification_code_resent'));
    }

    // ---------------------------
    // Helper to calculate resend / can_verify / message
    // ---------------------------
    private function getVerificationStatus($userOrVerification): array
    {
        $verification = $userOrVerification instanceof UserVerification
            ? $userOrVerification
            : $this->latestPhoneVerification($userOrVerification);

        if (!$verification) {
            return [0, false, __("auth.verification_code_not_found")];
        }

        $secondsSinceLast = now()->diffInSeconds($verification->created_at);
        $sendCount = $verification->send_count;

        if ($secondsSinceLast > last(UserVerification::ThrottleTimes)) {
            $sendCount = 0;
        }

        $waitSeconds = UserVerification::waitTimeForSendCount($sendCount);
        $resendSeconds = max($waitSeconds - $secondsSinceLast, 0);

        $canVerify = true;
        $message = null;

        if ($verification->hasTooManyAttempts()) {
            $canVerify = false;
            $message = __("auth.too_many_attempts_request_new");
        } elseif ($verification->isExpired()) {
            $canVerify = false;
            $message = __("auth.verification_code_expired");
        }

        return [$resendSeconds, $canVerify, $message];
    }

    private function latestPhoneVerification($user)
    {
        if (!$user) return null;
        return $user->verifications()
            ->where('type', UserVerification::TYPE_PHONE)
            ->latest()
            ->first();
    }

    private function humanReadableWait(int $seconds)
    {
        if ($seconds < 60) return __('auth.wait_seconds', ['seconds' => $seconds]);
        if ($seconds < 3600) return __('auth.wait_minutes', ['minutes' => floor($seconds / 60)]);
        if ($seconds < 86400) return __('auth.wait_hours', ['hours' => floor($seconds / 3600)]);
        return __('auth.wait_days', ['days' => floor($seconds / 86400)]);
    }
}
