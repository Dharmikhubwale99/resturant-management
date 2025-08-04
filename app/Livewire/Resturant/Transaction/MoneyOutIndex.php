<?php

namespace App\Livewire\Resturant\Transaction;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\MoneyOut;

class MoneyOutIndex extends Component
{
    public $search = '';
    public $from_date;
    public $to_date;

    #[Layout('components.layouts.resturant.app')]
    public function render()
    {
        $query = MoneyOut::query();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('party_name', 'like', "%{$this->search}%")
                  ->orWhere('description', 'like', "%{$this->search}%");
            });
        }

        if ($this->from_date) {
            $query->whereDate('date', '>=', $this->from_date);
        }

        if ($this->to_date) {
            $query->whereDate('date', '<=', $this->to_date);
        }

        $moneyOuts = $query->orderBy('date', 'desc')->paginate(15);


        return view('livewire.resturant.transaction.money-out-index', compact('moneyOuts'));
    }
}
