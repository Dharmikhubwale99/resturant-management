<?php

namespace App\Livewire\Admin\Admin;

use Livewire\Attributes\Layout;
use Livewire\Component;
use App\Models\User;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public $user;
    #[Layout('components.layouts.superadmin.app')]
    public function render()
    {
        return view('livewire.admin.admin.index', [
            'users' => User::paginate(10),
        ]);
    }
}
