<?php

namespace App\Http\Middleware;

use App\Helpers\ApiResponse;
use App\Helpers\CookiesHelper;
use App\Helpers\JWTHelper;
use Closure;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\UserToken;
use Illuminate\Support\Str;

class JwtMiddleware
{
    public function handle($request, Closure $next)
    {
        $accessToken  = $request->cookie('access_token');
        $refreshToken = $request->cookie('refresh_token');
        $cookieHelper = new CookiesHelper();
        $jwtHelper    = new JWTHelper();

        try {
            // 1️⃣ Validate Access Token
            if ($accessToken) {
                try {
                    $payload = JWT::decode($accessToken, new Key(env('JWT_SECRET'), 'HS256'));

                    if (isset($payload->exp) && time() > $payload->exp) {
                        throw new \Exception('Expired');
                    }

                    $user = User::find($payload->sub);
                    if (!$user) throw new \Exception('User not found');

                    Auth::setUser($user);
                    return $next($request);
                } catch (\Exception $e) {
                    // Expired → Try refresh
                }
            }

            // 2️⃣ Refresh Token
            if ($refreshToken) {
                $record = UserToken::where('refresh_token', hash('sha256', $refreshToken))
                    ->where('expires_at', '>', now())
                    ->where('revoked', false)
                    ->first();

                if (!$record) throw new \Exception('Invalid refresh token');

                $user = $record->user;

                // Rotate refresh token
                $newRefreshPlain = Str::random(64);
                $record->update([
                    'refresh_token' => hash('sha256', $newRefreshPlain),
                ]);

                // New access token
                $newAccessToken = $jwtHelper->generateJwt($user, 900);

                Auth::setUser($user);

                $cookieAccess  = $cookieHelper->generateCookie('access_token', $newAccessToken, $record->expires_at->diffInMinutes(now()));
                $cookieRefresh = $cookieHelper->generateCookie('refresh_token', $newRefreshPlain, $record->expires_at->diffInMinutes(now()));

                return $next($request)
                    ->cookie($cookieAccess)
                    ->cookie($cookieRefresh);
            }

            throw new \Exception('Unauthorized');
        } catch (\Exception $e) {
            $forgotAccess  = $cookieHelper->forgetCookie('access_token');
            $forgotRefresh = $cookieHelper->forgetCookie('refresh_token');

            return ApiResponse::error('Unauthorized', 401)
                ->cookie($forgotAccess)
                ->cookie($forgotRefresh);
        }
    }
}
