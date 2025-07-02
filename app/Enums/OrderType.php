<?php

namespace App\Enums;

enum OrderType: string
{
    case Dine_In = 'dine_in';
    case Takeaway = 'takeaway';
    case Delivery = 'delivery';

    public function label(): string
    {
        return match($this) {
            self::Dine_In => 'Dine_In',
            self::Takeaway => 'Takeaway',
            self::Delivery => 'Delivery',
        };
    }
}
