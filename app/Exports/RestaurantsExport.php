<?php

namespace App\Exports;

use App\Models\Restaurant;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class RestaurantsExport implements FromCollection, WithHeadings
{
    protected $filters;

    public function __construct(array $filters)
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = Restaurant::with('plan');

        // Apply plan type filter
        if ($this->filters['planType'] === 'free') {
            $query->whereHas('plan', function ($q) {
                $q->where('price', 0);
            });
        } elseif ($this->filters['planType'] === 'paid') {
            $query->whereHas('plan', function ($q) {
                $q->where('price', '>', 0);
            });
        }

        // Apply date filter
        switch ($this->filters['period']) {
            case 'today':
                $query->whereDate('created_at', Carbon::today());
                break;
            case 'monthly':
                $query->whereBetween('created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()]);
                break;
            case 'custom':
                if ($this->filters['startDate'] && $this->filters['endDate']) {
                    $query->whereBetween('created_at', [
                        Carbon::parse($this->filters['startDate'])->startOfDay(),
                        Carbon::parse($this->filters['endDate'])->endOfDay()
                    ]);
                }
                break;
        }

        return $query->get()->map(function ($resto) {
            return [
                'ID' => $resto->id,
                'Restaurant Name' => $resto->name,
                'Plan' => $resto->plan->name ?? '-',
                'Price' => $resto->plan->price ?? 0,
                'Mobile' => $resto->mobile,
                'Created At' => $resto->created_at ? Carbon::parse($resto->created_at)->format('d-m-Y') : '-',
                'Expiry Date' => $resto->plan_expiry_at ? Carbon::parse($resto->plan_expiry_at)->format('d-m-Y') : '-'
            ];
        });
    }

    public function headings(): array
    {
        return [
            'ID',
            'Restaurant Name',
            'Plan',
            'Price',
            'Mobile',
            'Created At',
            'Expiry Date'
        ];
    }
}