<?php

namespace App\Enums;

enum ItemType: string
{
    case NON_VEG = 'non_veg';
    case VEG = 'veg';
    case BEVERAGE = 'beverage';

    public function label(): string
    {
        return match($this) {
            self::NON_VEG => 'Non-Veg',
            self::VEG => 'Veg',
            self::BEVERAGE => 'Beverage',
        };
    }
}