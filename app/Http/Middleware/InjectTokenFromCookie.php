<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class InjectTokenFromCookie
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        if ($token = $request->cookie('token')) {
            $request->headers->set('Authorization', 'Bearer ' . $token);
        }
        // $sessionId = $request->cookie('session_id');

        // if ($sessionId && Session::has('user_token')) {
        //     $request->headers->set('Authorization', 'Bearer ' . Session::get('user_token'));
        // }


        try {
            return $next($request);
        } catch (\Illuminate\Auth\AuthenticationException $e) {
            return response()->json([
                'message' => 'Token expired or invalid',
            ], 401);
        }
    }
}
