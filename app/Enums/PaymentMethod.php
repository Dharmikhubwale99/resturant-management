<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case Cash = 'cash';
    case Card = 'card';
    case UPI = 'upi';
    case Duo = 'duo';
    case Part = 'part';

    public function label(): string
    {
        return match ($this) {
            self::Cash => 'Cash',
            self::Card => 'Card',
            self::UPI => 'UPI',
            self::Duo => 'Due',
            self::Part => 'Part',
        };
    }

    public static function options(): array
    {
        return array_map(fn($case) => $case->label(), self::cases());
    }
}
