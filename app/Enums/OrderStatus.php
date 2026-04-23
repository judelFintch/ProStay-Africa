<?php

namespace App\Enums;

enum OrderStatus: string
{
    case Draft = 'draft';
    case Confirmed = 'confirmed';
    case Served = 'served';
    case Closed = 'closed';
    case Cancelled = 'cancelled';
}
