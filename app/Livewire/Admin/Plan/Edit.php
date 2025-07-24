<?php

namespace App\Livewire\Admin\Plan;

use App\Models\Plan;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithFileUploads;

class Edit extends Component
{
    use WithFileUploads;
    public $plan, $name, $price, $duration_days, $description;
    public $images;

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

     public function removeImage($mediaId)
    {
        $this->plan->deleteMedia($mediaId);
        $this->plan->refresh();
    }

    public function submit()
{
    $this->validate([
        'name' => 'required|string|max:255',
        'price' => 'nullable|numeric|min:0',
        'duration_days' => 'nullable|numeric|min:0',
        'description' => 'nullable|string|max:255',
        'images' => 'nullable|image|max:2048',
    ]);

    $this->plan->update([
        'name' => $this->name,
        'price' => $this->price,
        'duration_days' => $this->duration_days,
        'description' => $this->description,
    ]);

    //  If a new image is uploaded, delete the old ones and replace
    if ($this->images) {
        // Delete existing images in the media collection
        $this->plan->clearMediaCollection('planImages');

        // Store and attach new image
        $storedPath = $this->images->store('plans', 'public');

        $this->plan->addMedia(storage_path('app/public/' . $storedPath))
                   ->usingFileName($this->images->getClientOriginalName())
                   ->toMediaCollection('planImages');
    }

    return redirect()->route('superadmin.plans.index')
        ->with('success', 'Plan updated successfully.');
}


}
