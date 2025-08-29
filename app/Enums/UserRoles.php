<?php

namespace App\Enums;

enum UserRoles: string
{
    case Admin   = 'admin';
    case Client    = 'client';
    case Artisan = 'artisan';
}
