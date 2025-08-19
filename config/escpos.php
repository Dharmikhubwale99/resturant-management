<?php
return [
    'driver'   => env('ESCPOS_DRIVER', 'network'),
    'host'     => env('ESCPOS_HOST', '127.0.0.1'),
    'port'     => (int) env('ESCPOS_PORT', 9100),
    'timeout'  => (int) env('ESCPOS_TIMEOUT', 30),

    'printer_name' => env('ESCPOS_PRINTER_NAME', ''),
    'profile'      => env('ESCPOS_PROFILE', 'default'),
    'width_cols'   => (int) env('ESCPOS_WIDTH_CHARS', 32),
    'currency'     => env('ESCPOS_CURRENCY', '₹'),
];
