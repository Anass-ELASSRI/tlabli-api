<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\ApiResponse;
use App\Helpers\CookiesHelper;
use App\Helpers\JWTHelper;
use App\Http\Controllers\Controller;
use App\Models\UserToken;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RefreshController extends Controller
{
    public function refresh(Request $request)
    {
        $cookieHelper = new CookiesHelper();
        $JWThelper    = new JWTHelper();

        try {
            $refreshToken = $request->cookie('refresh_token');
            if (!$refreshToken) {
                throw new \Exception('No refresh token');
            }

            // Find token record by hashed token (sha256)
            $tokenRecord = UserToken::where('refresh_token', hash('sha256', $refreshToken))
                ->where('expires_at', '>', now())
                ->first();

            if (!$tokenRecord) {
                throw new \Exception('Invalid or expired refresh token');
            }

            // Optional security checks: IP & UA
            if ($tokenRecord->ip_address && $tokenRecord->ip_address !== $request->ip()) {
                throw new \Exception('IP mismatch');
            }
            if ($tokenRecord->user_agent && $tokenRecord->user_agent !== $request->userAgent()) {
                throw new \Exception('User-Agent mismatch');
            }

            $user = $tokenRecord->user;
            if (!$user) throw new \Exception('User not found');

            // Rotate refresh token (preserve expiry)
            $newRefreshPlain = Str::random(64);
            $tokenRecord->update([
                'refresh_token' => hash('sha256', $newRefreshPlain),
            ]);

            // Create new access token (seconds)
            $accessTtlSeconds = 15 * 60; // 15 min
            $newAccessToken = $JWThelper->generateJwt($user, $accessTtlSeconds);

            // Build cookies
            $accessCookie  = $cookieHelper->generateCookie('access_token', $newAccessToken, intdiv($accessTtlSeconds, 60));
            $refreshMinutes = $tokenRecord->expires_at->diffInMinutes(now());
            $refreshCookie = $cookieHelper->generateCookie('refresh_token', $newRefreshPlain, $refreshMinutes);

            // Return fresh user info + set cookies
            $resp = ApiResponse::success(null, 'access token refreshed');

            return $resp->cookie($accessCookie)->cookie($refreshCookie);
        } catch (\Exception $e) {
            // Clear cookies on any failure
            $forgotAccess  = $cookieHelper->forgetCookie('access_token');
            $forgotRefresh = $cookieHelper->forgetCookie('refresh_token');

            return ApiResponse::error($e->getMessage(), 401)
                ->cookie($forgotAccess)
                ->cookie($forgotRefresh);
        }
    }
}
