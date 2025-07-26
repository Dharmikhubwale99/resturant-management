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
    public $search = '';
    public $confirmingBlock = false;
    public $blockId = null;

    #[Layout('components.layouts.admin.app')]
    public function render()
    {
        $plans = plan::when($this->search, function($query) {
            $query->where('name', 'like', '%' . $this->search . '%');
        })->orderByDesc('id')->paginate(10);
        return view('livewire.admin.plan.index', [
                'plans' => $plans
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

    public function confirmBlock($id)
    {
        $this->blockId = $id;
        $this->confirmingBlock = true;
    }

    public function cancelBlock()
    {
        $this->blockId = null;
        $this->confirmingBlock = false;
    }

    public function toggleBlock()
    {
        $plan = Plan::findOrFail($this->blockId);
        $plan->is_active = !$plan->is_active;
        $plan->save();

        $status = $plan->is_active ? 'unblocked' : 'blocked';
        session()->flash('message', "Plan {$status} successfully.");

        $this->cancelBlock();
    }
}

