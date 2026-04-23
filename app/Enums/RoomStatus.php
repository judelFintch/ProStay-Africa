<?php

namespace App\Enums;

enum RoomStatus: string
{
    case Available = 'available';
    case Occupied = 'occupied';
    case Cleaning = 'cleaning';
    case Maintenance = 'maintenance';
}
