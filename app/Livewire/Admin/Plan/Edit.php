<?php

namespace App\Livewire\Admin\Plan;

use App\Models\{Plan, AppConfiguration};
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithFileUploads;

class Edit extends Component
{
    use WithFileUploads;
    public $plan, $name, $price, $duration_days, $description;
    public $featureAccess = [];
    public $availableFeatures = [];
    public $type;
    public $value;
    public $amount;
    public $selectAllFeatures = false;
    public $storage_quota_mb, $max_file_size_kb;

    #[Layout('components.layouts.admin.app')]
    public function render()
    {
        return view('livewire.admin.plan.edit');
    }

    public function mount($id)
    {
        $this->plan = Plan::findOrFail($id);
        $this->fill($this->plan->only('name', 'price', 'duration_days', 'description','storage_quota_mb','max_file_size_kb'));

        $this->availableFeatures = AppConfiguration::all()->pluck('key')->toArray();

        $this->featureAccess = $this->plan->planFeatures()->where('is_active', true)->pluck('feature')->toArray();
        $this->selectAllFeatures = count($this->featureAccess) === count($this->availableFeatures);

        $this->plan = Plan::find($id);
        $this->fill($this->plan->only('name', 'price', 'duration_days', 'description', 'type', 'value', 'amount'));
    }

    public function removeImage($mediaId)
    {
        $this->plan->deleteMedia($mediaId);
        $this->plan->refresh();
    }

    public function updatedSelectAllFeatures($value)
    {
        if ($value) {
            $this->featureAccess = $this->availableFeatures;
        } else {
            $this->featureAccess = [];
        }
    }

    public function updatedFeatureAccess()
    {
        $this->selectAllFeatures = count($this->featureAccess) === count($this->availableFeatures);
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
        ];

        if ($this->type === 'percentage') {
            $rules['value'] = 'nullable|numeric|min:0';
        } elseif ($this->type === 'fixed') {
            $rules['amount'] = 'nullable|numeric|min:0';
        }

        $this->validate($rules);

        $this->plan->update([
            'name' => $this->name,
            'price' => $this->price,
            'duration_days' => $this->duration_days,
            'description' => $this->description,
            'type' => $this->type,
            'value' => $this->value,
            'amount' => $this->amount,
            'storage_quota_mb' => $this->storage_quota_mb,
            'max_file_size_kb' => $this->max_file_size_kb,
        ]);

        $this->plan->planFeatures()->delete();
        foreach ($this->featureAccess as $featureKey) {
            $this->plan->planFeatures()->create([
                'feature' => $featureKey,
                'is_active' => true,
            ]);
        }

        return redirect()->route('superadmin.plans.index')->with('success', 'Plan updated successfully.');
    }
}
