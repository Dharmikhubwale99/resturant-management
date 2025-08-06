<?php

namespace App\Livewire\Resturant;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\{Plan, AppConfiguration, PlanFeature};

class PlanPurchase extends Component
{
    #[Layout('components.layouts.auth.app')]
    public function render()
    {
        $plans = Plan::where('is_active', 0)->get();
        $modules = AppConfiguration::all();

        $planFeatures = PlanFeature::all()->groupBy('plan_id');

        return view('livewire.resturant.plan-purchase', [
            'plans' => $plans,
            'modules' => $modules,
            'planFeatures' => $planFeatures
        ]);
    }
}
