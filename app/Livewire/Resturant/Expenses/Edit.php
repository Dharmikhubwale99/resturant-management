<?php

namespace App\Livewire\Resturant\Expenses;

use App\Models\{Expense, SalesSummaries, User, Customer};
use Livewire\Component;
use Livewire\Attributes\Layout;

class Edit extends Component
{
    public $restaurant;
    public $expense_type_id;
    public $name;
    public $amount;
    public $paid_at;
    public $description;
    public $expenseTypes;
    public $expense;
    public $expense_total;
    public $partyOptions = [];

    #[Layout('components.layouts.resturant.app')]
    public function render()
    {
        return view('livewire.resturant.expenses.edit');
    }

    public function mount($id)
{
    if (!setting('expenses')) {
        abort(403, 'You do not have access to this module.');
    }

    $this->restaurant = auth()->user()->restaurants()->first();
    $this->expense = Expense::findOrFail($id);
    $this->expense_type_id = $this->expense->expense_type_id;
    $this->amount = $this->expense->amount;
    $this->paid_at = $this->expense->paid_at ? \Carbon\Carbon::parse($this->expense->paid_at)->format('Y-m-d') : null;
    $this->description = $this->expense->description;

    // Build dropdown options
    $users = User::where('restaurant_id', $this->restaurant->id)
        ->where('is_active', 0)
        ->get()
        ->mapWithKeys(fn($u) => ["user:$u->id" => "👤 $u->name"])
        ->toArray();

    $customers = Customer::where('restaurant_id', $this->restaurant->id)
        ->where('is_active', 0)
        ->get()
        ->mapWithKeys(fn($c) => ["customer:$c->id" => "🧾 $c->name"])
        ->toArray();

    $this->partyOptions = $users + $customers;

    // Pre-select current value
    if ($this->expense->user_id) {
        $this->name = 'user:' . $this->expense->user_id;
    } elseif ($this->expense->customer_id) {
        $this->name = 'customer:' . $this->expense->customer_id;
    } else {
        $this->name = null;
    }

    $this->expenseTypes = $this->restaurant->expenseTypes()->where('is_active', 0)->pluck('name', 'id')->toArray();
}

    public function submit()
    {
        if (setting('expense-type-module')) {
            $this->validate([
                'expense_type_id' => 'required',
            ]);
        }
        $this->validate([
            'name' => 'required',
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

        $this->expense->update([
            'expense_type_id' => $this->expense_type_id,
            'user_id' => $user_id,
            'customer_id' => $customer_id,
            'name' => null, // always null
            'amount' => $this->amount,
            'paid_at' => $this->paid_at,
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

        return redirect()->route('restaurant.expenses.index')->with('success', 'Expense updated successfully.');
    }
}
