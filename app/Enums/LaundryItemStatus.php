<?php

namespace App\Enums;

enum LaundryItemStatus: string
{
    case Dirty = 'dirty';
    case Washing = 'washing';
    case Clean = 'clean';
    case Distributed = 'distributed';
}
