<?php

namespace App\Helpers;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JWTHelper{
    public function generateJwt($user, $exp) {
        $payload = [
            'sub' => $user->id,
            'status' => $user->status,
            'role' => $user->role,
            'full_name' => $user->full_name,
            'exp' => time() + $exp,
        ];
    
        return JWT::encode($payload, env('JWT_SECRET'), 'HS256');
    }
    public function decodeJwt($jwt) {
        return JWT::decode($jwt, new Key(env('JWT_SECRET'), 'HS256'));
    }
}
