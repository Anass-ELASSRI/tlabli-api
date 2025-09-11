<?php


namespace App\Http\Middleware;

use App\Helpers\CookiesHelper;
use App\Helpers\JWTHelper;
use Closure;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Models\UserToken;
use Illuminate\Support\Str;

class SilentRefreshMiddleware
{
    public function handle($request, Closure $next)
    {
        $accessToken  = $request->cookie('access_token');
        $refreshToken = $request->cookie('refresh_token');
        $cookieHelper = new CookiesHelper();
        $jwtHelper    = new JWTHelper();

        // If access token is missing or expired but refresh exists → refresh silently
        if ($refreshToken && (!$accessToken || $this->isExpired($accessToken))) {
            $record = UserToken::where('refresh_token', hash('sha256', $refreshToken))
                ->where('expires_at', '>', now())
                ->where('revoked', false)
                ->first();

            if ($record) {
                $user = $record->user;

                $newRefreshPlain = Str::random(64);
                $record->update([
                    'refresh_token' => hash('sha256', $newRefreshPlain),
                ]);

                $newAccessToken = $jwtHelper->generateJwt($user, 900);

                $cookieAccess  = $cookieHelper->generateCookie('access_token', $newAccessToken, $record->expires_at->diffInMinutes(now()));
                $cookieRefresh = $cookieHelper->generateCookie('refresh_token', $newRefreshPlain, $record->expires_at->diffInMinutes(now()));

                return $next($request)
                    ->cookie($cookieAccess)
                    ->cookie($cookieRefresh);
            }
        }

        // Continue even if refresh fails
        return $next($request);
    }

    private function isExpired(?string $jwt): bool
    {
        if (!$jwt) return true;
        try {
            $payload = JWT::decode($jwt, new Key(env('JWT_SECRET'), 'HS256'));
            return isset($payload->exp) && time() > $payload->exp;
        } catch (\Exception $e) {
            return true;
        }
    }
}
