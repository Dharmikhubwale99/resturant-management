<?php

namespace App\Livewire\Admin\Plan;

use App\Models\Plan;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

class Index extends Component
{
    use WithPagination;

    public $confirmingDelete = false;
    public $planToDelete = null;

    #[Layout('components.layouts.superadmin.app')]
    public function render()
    {
        return view('livewire.admin.plan.index', [
            'plans' => Plan::paginate(10)  // Adjust page size as needed
        ]);
    }

    public function confirmDelete($id)
    {
        $this->planToDelete = $id;
        $this->confirmingDelete = true;
    }

    public function cancelDelete()
    {
        $this->confirmingDelete = false;
        $this->planToDelete = null;
    }

    public function deletePlan()
    {
        $plan = Plan::find($this->planToDelete);
        if ($plan) {
            $plan->delete();
            session()->flash('success', 'Plan deleted successfully.');
        } else {
            session()->flash('error', 'Plan not found.');
        }

        $this->cancelDelete();
    }
}

