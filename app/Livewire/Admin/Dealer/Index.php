<?php

namespace App\Livewire\Admin\Dealer;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\User;
use Livewire\WithPagination;

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
        $users = User::role(['dealer','superadmin'])
        ->where(function ($query) {
            $query->where('users.name', 'like', '%' . $this->search . '%')
                  ->orWhere('users.mobile', 'like', '%' . $this->search . '%');
        })
        ->orderBy('users.created_at', 'desc')
        ->paginate(10);
        return view('livewire.admin.dealer.index', [
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

        $user->is_active = !$user->is_active;
        $user->save();

        $status = $user->is_active ? 'unblocked' : 'blocked';
        session()->flash('message', "User {$status} successfully.");

        $this->cancelBlock();
    }
}
