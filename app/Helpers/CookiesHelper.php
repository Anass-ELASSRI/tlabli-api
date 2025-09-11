<?php

namespace App\Helpers;

class CookiesHelper
{
    public function generateCookie($name, $value, $exp)
    {
        return cookie(
            $name,
            $value,
            $exp,
            '/',       // path
            null,      // domain (null works for localhost)
            true,      // secure
            true,      // httpOnly
            false,
            'lax'
        );
    }

    public function forgetCookie($name)
    {
        return cookie($name, '/', -1);
    }
}
