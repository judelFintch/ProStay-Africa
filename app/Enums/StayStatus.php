<?php

namespace App\Enums;

enum StayStatus: string
{
    case Active = 'active';
    case CheckedOut = 'checked_out';
    case Cancelled = 'cancelled';
}
