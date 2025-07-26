<?php

namespace App\Livewire\Resturant;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Plan;

class PlanPurchase extends Component
{
    #[Layout('components.layouts.auth.app')]
    public function render()
    {
        $plan = Plan::where('is_active',0)->get();
        return view('livewire.resturant.plan-purchase',[
            'plans' => $plan
        ]);
    }
}
