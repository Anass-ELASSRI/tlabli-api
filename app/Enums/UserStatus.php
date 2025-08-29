<?php

namespace App\Enums;

enum UserStatus: string
{
    case Pending           = 'pending';
    case ProfileIncomplete = 'profile_incomplete';
    case ProfilePending    = 'profile_pending';
    case ProfileRejected   = 'profile_rejected';
    case Active            = 'active';
    case Suspended         = 'suspended';


    // Optional: helper method for listing all values
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
