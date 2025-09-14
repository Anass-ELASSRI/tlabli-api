<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserVerification;
use Illuminate\Support\Facades\DB;

class UserVerificationService
{
    /**
     * Create a new verification code for a user.
     */
    public function createCode(User $user, string $type, int $expiresMinutes = 5, int $digits = 6, array $meta = []): UserVerification
    {
        $this->ensureUserExists($user);
        $this->ensureValidType($type);

        $latest = $this->latest($user, $type);
        $sendCount = $latest?->normalizedSendCount() ?? 0;

        $code = random_int(pow(10, $digits - 1), pow(10, $digits) - 1);

        return DB::transaction(function () use ($user, $type, $code, $expiresMinutes, $sendCount, $meta) {
            return $user->verifications()->create([
                'type' => $type,
                'code' => $code,
                'expires_at' => now()->addMinutes($expiresMinutes),
                'attempts' => 0,
                'send_count' => $sendCount + 1,
                'meta' => $meta,
            ]);
        });
    }

    /**
     * Get the latest verification record for a user and type.
     */
    public function latest(User $user, string $type): ?UserVerification
    {
        $this->ensureUserExists($user);
        $this->ensureValidType($type);

        return $user->verifications()
            ->where('type', $type)
            ->latest()
            ->first();
    }

    /**
     * Validate a submitted code.
     */
    public function validateCode(User $user, string $type, string $code): bool
    {
        $this->ensureUserExists($user);
        $this->ensureValidType($type);

        $verification = $this->latest($user, $type);

        if (!$verification || $verification->isExpired() || $verification->hasTooManyAttempts()) {
            return false;
        }

        if ($verification->code !== $code) {
            $verification->increment('attempts');
            $verification->update(['last_attempt_at' => now()]);
            return false;
        }

        $verification->delete();
        return true;
    }

    /**
     * Resend verification code (with throttle)
     */
    public function resend(User $user, string $type, int $expiresMinutes = 5, int $digits = 6, array $meta = []): array
    {
        $this->ensureUserExists($user);
        $this->ensureValidType($type);

        $latest = $this->latest($user, $type);

        if ($latest) {
            [$resendSeconds, $canVerify] = $this->getStatus($latest);
            if ($resendSeconds > 0) {
                return [
                    'success' => false,
                    'resend_seconds' => $resendSeconds,
                    'can_verify' => $canVerify,
                    'message' => $this->humanReadableWait($resendSeconds),
                ];
            }
        }

        $verification = $this->createCode($user, $type, $expiresMinutes, $digits, $meta);

        $waitSeconds = UserVerification::waitTimeForSendCount($verification->send_count);

        return [
            'success' => true,
            'resend_seconds' => $waitSeconds,
            'code' => $verification->code, // optional for testing
        ];
    }

    /**
     * Human-readable wait string
     */
    public function humanReadableWait(int $seconds): string
    {
        if ($seconds < 60) return __('auth.wait_seconds', ['seconds' => $seconds]);
        if ($seconds < 3600) return __('auth.wait_minutes', ['minutes' => floor($seconds / 60)]);
        if ($seconds < 86400) return __('auth.wait_hours', ['hours' => floor($seconds / 3600)]);
        return __('auth.wait_days', ['days' => floor($seconds / 86400)]);
    }

    /**
     * ---------------------------
     * PRIVATE: Ensure type is valid
     * ---------------------------
     */
    private function ensureValidType(string $type): void
    {
        if (!in_array($type, [
            UserVerification::TYPE_PHONE,
            UserVerification::TYPE_EMAIL,
            UserVerification::TYPE_2FA,
        ])) {
            throw new \InvalidArgumentException("Invalid verification type: {$type}");
        }
    }

    /**
     * ---------------------------
     * PRIVATE: Ensure user exists
     * ---------------------------
     */
    private function ensureUserExists(User $user): void
    {
        if (!$user->exists) {
            throw new \InvalidArgumentException("User does not exist or has been deleted.");
        }
    }

    /**
     * Get verification status: resend cooldown, can verify, message
     */
    public function getStatus(User|UserVerification $userOrVerification, string $type = null): array
    {
        if ($userOrVerification instanceof User) {
            $this->ensureUserExists($userOrVerification);
            if ($type === null) {
                throw new \InvalidArgumentException("Type must be provided when passing a User.");
            }
            $this->ensureValidType($type);
            $verification = $this->latest($userOrVerification, $type);
        } else {
            $verification = $userOrVerification;
        }

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
}
