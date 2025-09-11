<?php

namespace App\Services\Auth;

use App\Helpers\CookiesHelper;
use App\Helpers\JWTHelper;
use App\Models\UserToken;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RefreshService
{
    protected $cookieHelper;
    protected $jwtHelper;

    public function __construct()
    {
        $this->cookieHelper = new CookiesHelper();
        $this->jwtHelper = new JWTHelper();
    }

    /**
     * Try refresh from refresh_token cookie.
     * 
     * Returns array on success:
     *  [
     *    'user' => User,
     *    'access_cookie' => Symfony\Component\HttpFoundation\Cookie,
     *    'refresh_cookie' => Symfony\Component\HttpFoundation\Cookie
     *  ]
     * 
     * Throws \Exception on failure.
     */
    public function refreshFromRequest(Request $request)
    {
        $refreshPlain = $request->cookie('refresh_token');

        if (!$refreshPlain) {
            throw new \Exception('No refresh token');
        }

        $hash = hash('sha256', $refreshPlain);

        $tokenRecord = UserToken::where('refresh_token', $hash)
            ->where('expires_at', '>', now())
            ->first();

        if (!$tokenRecord) {
            throw new \Exception('Invalid or expired refresh token');
        }

        // Optional security checks (enable as desired)
        if ($tokenRecord->ip_address && $tokenRecord->ip_address !== $request->ip()) {
            throw new \Exception('IP mismatch');
        }
        if ($tokenRecord->user_agent && $tokenRecord->user_agent !== $request->userAgent()) {
            throw new \Exception('User-Agent mismatch');
        }

        $user = $tokenRecord->user;
        if (!$user) {
            throw new \Exception('User not found');
        }

        // Rotate refresh token (preserve expires_at)
        $newRefreshPlain = Str::random(64);
        $tokenRecord->update([
            'refresh_token' => hash('sha256', $newRefreshPlain),
        ]);

        // Create new short-lived access token (seconds)
        $accessTtlSeconds = 15 * 60; // adjust as needed
        $newAccessToken = $this->jwtHelper->generateJwt($user, $accessTtlSeconds);

        // Build cookies using your CookiesHelper
        $accessCookie = $this->cookieHelper->generateCookie('access_token', $newAccessToken, intdiv($accessTtlSeconds, 60));
        $refreshMinutes = $tokenRecord->expires_at->diffInMinutes(now());
        $refreshCookie = $this->cookieHelper->generateCookie('refresh_token', $newRefreshPlain, $refreshMinutes);

        return [
            'user' => $user,
            'access_cookie' => $accessCookie,
            'refresh_cookie' => $refreshCookie,
            'access_expires_in' => $accessTtlSeconds,
        ];
    }

    /**
     * Forcibly clear cookies (helper)
     */
    public function forgetCookies()
    {
        return [
            'access' => $this->cookieHelper->forgetCookie('access_token'),
            'refresh' => $this->cookieHelper->forgetCookie('refresh_token'),
        ];
    }
}
