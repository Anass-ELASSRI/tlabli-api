<?php

namespace App\Http\Middleware;

use App\Helpers\ApiResponse;
use Closure;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Cookie;

class JwtMiddleware
{
    public function handle($request, Closure $next)
    {
        $token = $request->cookie('access_token');

        if (!$token) {
            return ApiResponse::error('Unauthorized', 401);
        }

        try {
            $payload = JWT::decode($token, new Key(env('JWT_SECRET'), 'HS256'));

            // Check expiration manually
            if (isset($payload->exp) && time() > $payload->exp) {
                // Token expired

                return ApiResponse::error('Access token expired', 401);
            }

            $user = User::find($payload->sub);
            if (!$user) {
                return ApiResponse::error('User not found', 401);
            }

            Auth::setUser($user);
        } catch (\Exception $e) {

            return ApiResponse::error('Invalid token', 401);
        }


        return $next($request);
    }
}
