<?php

namespace App\Livewire\Admin\Plan;

use App\Models\Plan;
use Livewire\Component;
use Livewire\Attributes\Layout;

class Edit extends Component
{
    public $plan, $name, $price, $duration_days, $description;

    #[Layout('components.layouts.admin.app')]
    public function render()
    {
        return view('livewire.admin.plan.edit');
    }

    public function mount($id)
    {
        $this->plan = Plan::find($id);
        $this->fill($this->plan->only('name', 'price', 'duration_days', 'description'));
    }
    public function submit()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'price' => 'nullable|numeric|min:0',
            'duration_days' => 'nullable|numeric|min:0',
            'description' => 'nullable|string|max:255', 
        ]);

        $this->plan->update([
            'name' => $this->name,
            'price' => $this->price,
            'duration_days' => $this->duration_days,
            'description' => $this->description
        ]);

        return redirect()->route('superadmin.plans.index')->with('success', 'Plan updated successfully.');
    }
}
