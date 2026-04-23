<?php

namespace App\Enums;

enum ServiceAreaCode: string
{
    case Accommodation = 'accommodation';
    case Restaurant = 'restaurant';
    case Bar = 'bar';
    case Terrace = 'terrace';
    case Laundry = 'laundry';
    case Pos = 'pos';
}
