<?php

namespace App\Livewire\Admin\Admin;

use App\Models\Restaurant;
use Livewire\Attributes\Layout;
use Livewire\Component;
use App\Models\User;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

class Index extends Component
{
    use WithPagination;

    public $confirmingDelete = false;
    public $deleteId;
    public $confirmingBlock = false;
    public $blockId = null;
    public $search = '';

    #[Layout('components.layouts.admin.app')]
    public function render()
{
    $users = User::role(['admin', 'superadmin'])
        ->with('restaurant')
        ->leftJoin('restaurants', 'users.id', '=', 'restaurants.user_id')
        ->where(function ($query) {
            $query->where('users.name', 'like', '%' . $this->search . '%')
                  ->orWhere('users.mobile', 'like', '%' . $this->search . '%')
                  ->orWhere('restaurants.name', 'like', '%' . $this->search . '%')
                  ->orWhere('restaurants.mobile', 'like', '%' . $this->search . '%')
                  ->orWhere('restaurants.plan_expiry_at', 'like', '%' . $this->search . '%');
        })
        ->select('users.*')
        ->orderBy('users.created_at', 'desc')
        ->paginate(10);

    return view('livewire.admin.admin.index', [
        'users' => $users,
    ]);
}


    public function confirmDelete($id)
    {
        $this->deleteId = $id;
        $this->confirmingDelete = true;
    }

    public function deleteUser()
    {
        User::find($this->deleteId)?->delete();
        Restaurant::where('user_id', $this->deleteId)->delete();
        $this->confirmingDelete = false;
        $this->deleteId = null;
        session()->flash('success', 'User deleted successfully.');
    }

    public function cancelDelete()
    {
        $this->confirmingDelete = false;
        $this->deleteId = null;
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
        $user = User::findOrFail($this->blockId);
        $restaurant = Restaurant::where('user_id', $user->id)->first();

        $user->is_active = !$user->is_active;
        $user->save();
        $restaurant->update([
            'is_active' => $user->is_active
        ]);

        $status = $user->is_active ? 'unblocked' : 'blocked';
        session()->flash('message', "Plan {$status} successfully.");

        $this->cancelBlock();
    }
}
