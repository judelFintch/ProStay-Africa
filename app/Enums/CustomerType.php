<?php

namespace App\Enums;

enum CustomerType: string
{
    case Lodged = 'lodged';
    case WalkInIdentified = 'walk_in_identified';
    case WalkInAnonymous = 'walk_in_anonymous';
}
