<?php

namespace App\Exports;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class StaffWiseExport implements FromCollection, WithHeadings, WithMapping
{
    protected $fromDate;
    protected $toDate;
    protected $restaurantId;

    public function __construct($fromDate, $toDate, $restaurantId)
    {
        $this->fromDate = $fromDate;
        $this->toDate = $toDate;
        $this->restaurantId = $restaurantId;
    }

    public function collection()
    {
        return User::whereHas('orders', function ($query) {
                $query->where('restaurant_id', $this->restaurantId);
            })
            ->withCount(['orders as total_orders' => function ($query) {
                $query->where('restaurant_id', $this->restaurantId)
                      ->whereBetween('created_at', [$this->fromDate, $this->toDate]);
            }])
            ->withSum(['orders as total_sales' => function ($query) {
                $query->where('restaurant_id', $this->restaurantId)
                      ->whereBetween('created_at', [$this->fromDate, $this->toDate]);
            }], 'total_amount')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Name',
            'Phone',
            'Total Orders',
            'Total Sales (₹)',
        ];
    }

    public function map($staff): array
    {
        return [
            $staff->name,
            $staff->mobile ?? '-',
            $staff->total_orders ?? 0,
            number_format($staff->total_sales ?? 0, 2),
        ];
    }
}
