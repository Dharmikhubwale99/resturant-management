<?php

namespace App\Livewire\Admin\Plan;

use Livewire\Component;
use App\Models\Plan;
use Livewire\Attributes\Layout;

class Create extends Component
{
    #[Layout('components.layouts.superadmin.app')]
    public function render()
    {
        return view('livewire.admin.plan.create');
    }

     public function submit()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        Plan::create([
            'name' => $this->name,
            'price' => $this->price,
            'description' => $this->description,
        ]);

        session()->flash('success', 'Plan created successfully!');
        $this->reset(['name', 'price', 'description']);

        return redirect()->route('admin.plans.index');
    }
}
