<?php

namespace App\Livewire\Admin\Plan;

use Livewire\Component;
use App\Models\{Plan, AppConfiguration};
use Livewire\Attributes\Layout;
use Livewire\WithFileUploads;

class Create extends Component
{
    use WithFileUploads;
    public $name, $price, $duration_days, $description;
    public $plan;
    public $type;
    public $value;
    public $amount;
    public $featureAccess = [];
    public $availableFeatures = [];
    public $selectAllFeatures = false;
    public $storage_quota_mb, $max_file_size_kb;
    public $machine_price;
    public $machine_discount_type;
    public $machine_discount_value;
    public $machine_discount_amount;
    public $machine_final_amount;

    #[Layout('components.layouts.admin.app')]
    public function render()
    {
        return view('livewire.admin.plan.create');
    }

    public function mount()
    {
        $this->availableFeatures = AppConfiguration::all()->pluck('key')->toArray();
    }

    public function updatedSelectAllFeatures($value)
    {
        if ($value) {
            $this->featureAccess = $this->availableFeatures;
        } else {
            $this->featureAccess = [];
        }
    }


    public function submit()
    {
        $rules = [
            'name' => 'required|string|max:255',
            'price' => 'nullable|numeric|min:0',
            'duration_days' => 'nullable|numeric|min:0',
            'description' => 'nullable|string|max:255',
            'type' => 'nullable',
            'storage_quota_mb' => 'nullable|integer|min:0',
            'max_file_size_kb' => 'nullable|integer|min:0',
            'machine_price' => 'nullable|numeric|min:0',
            'machine_discount_type' => 'nullable|in:fixed,percentage',
        ];

        if ($this->type === 'percentage') {
            $rules['value'] = 'nullable|numeric|min:0';
        } elseif ($this->type === 'fixed') {
            $rules['amount'] = 'nullable|numeric|min:0';
        }

        if ($this->machine_discount_type === 'percentage') {
            $rules['machine_discount_value'] = 'required|numeric|min:0|max:100';
        } elseif ($this->machine_discount_type === 'fixed') {
            $rules['machine_discount_amount'] = 'required|numeric|min:0|lte:machine_price';
        }

        $validated = $this->validate($rules);

        $this->plan = Plan::create([
            'name' => $this->name,
            'price' => $this->price,
            'duration_days' => $this->duration_days,
            'description' => $this->description,
            'type' => $this->type,
            'value' => $this->value,
            'amount' => $this->amount,
            'storage_quota_mb' => $this->storage_quota_mb,
            'max_file_size_kb' => $this->max_file_size_kb,
            'machine_price' => $this->machine_price,
            'machine_discount_type' => $this->machine_discount_type,
            'machine_discount_value' => $this->machine_discount_value,
            'machine_final_amount' => $this->machine_discount_amount,
        ]);

        foreach ($this->featureAccess as $featureKey) {
            $this->plan->planFeatures()->create([
                'feature' => $featureKey,
                'is_active' => true,
                'belongs_to' => 'restaurant',
            ]);
        }


        session()->flash('success', 'Plan created successfully!');
        $this->reset(['name', 'price', 'description']);

        return redirect()->route('superadmin.plans.index');
    }
}
