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

    #[Layout('components.layouts.admin.app')]
    public function render()
    {
        $users = User::role(['admin', 'superadmin'])
        ->leftJoin('restaurants', 'users.id', '=', 'restaurants.user_id')
        ->select('users.*', 'restaurants.plan_expiry_at', 'restaurants.created_at as restaurant_created_at')
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
}
