<?php

namespace App\Services\Auth;

use App\Helpers\CookiesHelper;
use App\Helpers\JWTHelper;
use App\Models\UserToken;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Jenssegers\Agent\Agent;

class AuthService
{
    protected $cookieHelper;
    protected $jwtHelper;

    public function __construct()
    {
        $this->cookieHelper = new CookiesHelper();
        $this->jwtHelper = new JWTHelper();
    }

    public function login($user, Request $request)
    {
        // 1️⃣ Create access token (15 min)
        $accessToken = $this->jwtHelper->generateJwt($user, 900);

        // 2️⃣ Create refresh token (7 days)
        $refreshPlain = Str::random(64);
        $expiresAt    = now()->addDays(7);

        // Detect device
        $agent = new Agent();
        $agent->setUserAgent($request->userAgent());
        $deviceName = $request->header('X-Device-Name')
            ?? $agent->platform() . ' - ' . $agent->browser();

        // Store in DB
        UserToken::create([
            'user_id'      => $user->id,
            'refresh_token' => hash('sha256', $refreshPlain),
            'ip_address'   => $request->ip(),
            'user_agent'   => $request->userAgent(),
            'device_name'  => $deviceName,
            'expires_at'   => $expiresAt,
        ]);

        // Cookies
        $cookieAccess  = $this->cookieHelper->generateCookie('access_token', $accessToken, 60 * 24 * 7);
        $cookieRefresh = $this->cookieHelper->generateCookie('refresh_token', $refreshPlain, 60 * 24 * 7);

        return [
            'cookieAccess' => $cookieAccess,
            'cookieRefresh' => $cookieRefresh
        ];
    }

    /**
     * Forcibly clear cookies (helper)
     */
    public function logout(Request $request)
    {
        $refreshToken = $request->cookie('refresh_token');

        if ($refreshToken) {
            UserToken::where('user_id', $request->user()->id)->where('refresh_token', hash('sha256', $refreshToken))
                ->update(['revoked' => true]);
        }

        $cookieHelper = new CookiesHelper();
        $forgotAccess  = $cookieHelper->forgetCookie('access_token');
        $forgotRefresh = $cookieHelper->forgetCookie('refresh_token');

        return [
            'forgotAccess' => $forgotAccess,
            'forgotRefresh' => $forgotRefresh
        ];
    }
}
