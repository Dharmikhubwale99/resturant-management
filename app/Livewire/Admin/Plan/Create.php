<?php

namespace App\Livewire\Admin\Plan;

use Livewire\Component;
use App\Models\Plan;
use Livewire\Attributes\Layout;
use Livewire\WithFileUploads;

class Create extends Component
{
    use WithFileUploads;
    public $name, $price, $duration_days, $description;
    public $images = [];
    public $plan;

    #[Layout('components.layouts.admin.app')]
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

        $this->plan = Plan::create([
            'name' => $this->name,
            'price' => $this->price,
            'duration_days' => $this->duration_days,
            'description' => $this->description,
        ]);

        foreach ($this->images as $image) {
            $this->plan->addMedia($image)->toMediaCollection('images');
        }

        session()->flash('success', 'Plan created successfully!');
        $this->reset(['name', 'price', 'description']);

        return redirect()->route('superadmin.plans.index');
    }
}
