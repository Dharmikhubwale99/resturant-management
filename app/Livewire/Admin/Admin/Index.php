<?php

namespace App\Livewire\Admin\Admin;

use Livewire\Attributes\Layout;
use Livewire\Component;
use App\Models\User;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

class Index extends Component
{
    use WithPagination;

    #[Layout('components.layouts.admin.app')]
    public function render()
    {
        $users = User::role(['admin', 'superadmin'])
        ->leftJoin('restaurants', 'users.id', '=', 'restaurants.user_id')
        ->select('users.*', 'restaurants.plan_expiry_at')
        ->paginate(10);

        return view('livewire.admin.admin.index', [
            'users' => $users,
        ]);
    }
}
