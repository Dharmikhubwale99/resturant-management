<?php

namespace App\Livewire\Waiter\AdvancBook;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Customer;
use App\Models\Table;
use App\Models\{TableBooking, Restaurant};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
class Create extends Component
{
    public $tables;
    public $selectedTable = '';
    public $name, $mobile, $booking_time;

    #[Layout('components.layouts.resturant.app')]
    public function render()
    {
        return view('livewire.waiter.advanc-book.create');
    }

    public function mount()
    {
        $this->tables = Table::all();
    }

    public function saveBooking()
    {
        $this->validate([
            'selectedTable' => 'required|exists:tables,id',
            'name' => 'required|string',
            'mobile' => 'required|digits:10',
            'booking_time' => 'required|date|after:now',
        ]);

        DB::transaction(function () {
            if (auth()->user()->restaurant_id) {
                $restaurantId = auth()->user()->restaurant_id;
            } else {
                $restaurantId = Restaurant::where('user_id', auth()->id())->value('id');
            }

            $customer = Customer::create([
                'name' => $this->name,
                'mobile' => $this->mobile,
                'restaurant_id' => $restaurantId,
                'is_active' => true,
            ]);

            $customerId = $customer->id;

            TableBooking::create([
                'customer_id' => $customerId,
                'restaurant_id' => $restaurantId,
                'table_id' => $this->selectedTable,
                'booking_time' => $this->booking_time,
                'status' => 'booked',
            ]);
        });

        session()->flash('success', 'Table booked successfully.');
        return redirect()->route('restaurant.advance-booking');
    }

}
