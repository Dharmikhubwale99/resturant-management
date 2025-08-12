<?php

namespace App\Livewire\Resturant;

use App\Models\{Order, Payment,RestaurantPaymentLog, Restaurant};
use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;

class Dashboard extends Component
{
    public $user;
    public $todayIncome = 0;
    public $todayIncomeProgress = 0;
    public $todayIncomePercentage = 0;
    public $todayMoney = 0;
    public $todayMoneyProgress = 0;
    public $todayMoneyPercentage = 0;
    public $todayOrders = 0;
    public $restaurantId;

    #[Layout('components.layouts.resturant.app')]
    public function render()
    {
        $orders = Order::with(['table'])
        ->where('restaurant_id', $this->restaurantId)
        ->orderByDesc('id')
        ->limit(50)
        ->get();

        return view('livewire.resturant.dashboard', [
            'orders' => $orders,
        ]);
    }

    public function mount()
    {
        $user = Auth::user();
        $this->user = $user;
        if ($user->restaurant_id) {
            $restaurantId = $user->restaurant_id;
        } else {
            $restaurantId = Restaurant::where('user_id', $user->id)->value('id');
        }

        $restaurant = Restaurant::find($restaurantId);
        $this->restaurantId = $restaurantId;

        if (empty($restaurant->name) || empty($restaurant->email) || empty($restaurant->mobile) || empty($restaurant->address) || empty($restaurant->pin_code_id)) {
            return redirect()->route('restaurant.resto-register')->with('info', 'Please complete your restaurant profile.');
        }

        $this->calculateTodayIncome($restaurant->id);
        $this->calculateTodayMoney($restaurant->id);
        $this->calculateTodayOrders($restaurant->id);
    }

    public function calculateTodayIncome($restaurantId)
    {
        $today = now()->format('Y-m-d');

        $this->todayIncome = Order::whereDate('created_at', $today)
            ->where('restaurant_id', $restaurantId)
            ->sum('total_amount');

        $targetIncome = 30000;

        $this->todayIncomeProgress = min(($this->todayIncome / $targetIncome) * 100, 100);
        $this->todayIncomePercentage = number_format(($this->todayIncome / $targetIncome) * 100, 1);
    }

    public function calculateTodayMoney($restaurantId)
    {
        $today = now()->format('Y-m-d');

        $paymentAmount = Payment::whereDate('created_at', $today)
            ->whereIn('method', ['cash', 'card', 'upi','part'])
            ->whereHas('order', function ($query) use ($restaurantId) {
                $query->where('restaurant_id', $restaurantId);
            })
            ->sum('amount');

        $logAmount = RestaurantPaymentLog::whereDate('created_at', $today)
            ->where('restaurant_id', $restaurantId)
            ->sum('paid_amount');

        $this->todayMoney = $paymentAmount + $logAmount;

        $targetMoney = 10000;

        $this->todayMoneyProgress = min(($this->todayMoney / $targetMoney) * 100, 100);
        $this->todayMoneyPercentage = number_format(($this->todayMoney / $targetMoney) * 100, 1);
    }

    public function calculateTodayOrders($restaurantId)
    {
        $today = now()->toDateString();

        $this->todayOrders = Order::whereDate('created_at', $today)
            ->where('restaurant_id', $restaurantId)
            ->count();
    }
}
