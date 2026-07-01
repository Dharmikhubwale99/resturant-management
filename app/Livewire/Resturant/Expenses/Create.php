<?php

namespace App\Livewire\Resturant\Expenses;

use App\Models\{Expense, SalesSummaries, User, Customer};
use Livewire\Component;
use Livewire\Attributes\Layout;

class Create extends Component
{
    public $restaurant;
    public $expense_type_id;
    public $name;
    public $amount;
    public $description;
    public $expenseTypes;
    public $paid_at;
    public $expense_total;
    public $partyOptions = [];

    #[Layout('components.layouts.resturant.app')]
    public function render()
    {
        $this->restaurant = auth()->user()->restaurants()->first();
        return view('livewire.resturant.expenses.create');
    }
    public function mount()
    {
        if (!setting('expenses')) {
        abort(403, 'You do not have access to this module.');
    }

    $this->restaurant = auth()->user()->restaurants()->first();

    $this->expenseTypes = $this->restaurant
                            ->expenseTypes()
                            ->where('is_active', 0)
                            ->orderBy('name')
                            ->pluck('name', 'id')
                            ->toArray();

    $users = User::where('restaurant_id', $this->restaurant->id)
        ->where('is_active', 0)
        ->get()
        ->mapWithKeys(fn($u) => ["user:$u->id" => " $u->name"])
        ->toArray();

    $customers = Customer::where('restaurant_id', $this->restaurant->id)
        ->where('is_active', 0)
        ->get()
        ->mapWithKeys(fn($c) => ["customer:$c->id" => " $c->name"])
        ->toArray();

    $this->partyOptions = $users + $customers; // maintain order

    
    $this->expense_total = SalesSummaries::where('restaurant_id', $this->restaurant->id)->first();
    }


    public function submit()
    {
        if (setting('expense-type-module')) {
            $this->validate([
                 'expense_type_id' => 'required',
            ]);
        }

        $this->validate([
            // 'name' => 'required',
            'amount' => 'required',
            'paid_at' => 'nullable',
            'description' => 'nullable',
        ]);

        $user_id = null;
        $customer_id = null;

        if (str_starts_with($this->name, 'user:')) {
            $user_id = explode(':', $this->name)[1];
        } elseif (str_starts_with($this->name, 'customer:')) {
            $customer_id = explode(':', $this->name)[1];
        }

        Expense::create([
            'restaurant_id' => $this->restaurant->id,
            'expense_type_id' => $this->expense_type_id,
            'customer_id' => $customer_id,
            'user_id' => $user_id,
            'name' => null,
            'amount' => $this->amount,
            'paid_at' =>$this->paid_at,
            'description' => $this->description,
        ]);

        if (!$this->expense_total || $this->expense_total->summary_date != now()->format('Y-m-d')) {
            SalesSummaries::create([
                'restaurant_id' => $this->restaurant->id,
                'total_expances' => $this->amount,
                'summary_date' => now(),
            ]);
        } else {
            $this->expense_total->update([
                'total_expances' => $this->expense_total->total_sale + $this->amount,
                'summary_date' => now(),
            ]);
        }


        return redirect()->route('restaurant.expenses.index')->with('success', 'Expense created successfully.');
    }
}
