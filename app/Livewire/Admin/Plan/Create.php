<?php

namespace App\Livewire\Admin\Plan;

use Livewire\Component;
use App\Models\Plan;
use Livewire\Attributes\Layout;

class Create extends Component
{
    public $name, $price, $duration_days, $description;

    #[Layout('components.layouts.superadmin.app')]
    public function render()
    {
        return view('livewire.admin.plan.create');
    }

     public function submit()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'price' => 'nullable|numeric|min:0',
            'duration_days' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        Plan::create([
            'name' => $this->name,
            'price' => $this->price,
            'duration_days' => $this->duration_days,
            'description' => $this->description,
        ]);

        session()->flash('success', 'Plan created successfully!');
        $this->reset(['name', 'price', 'description']);

        return redirect()->route('superadmin.plans.index');
    }
}
