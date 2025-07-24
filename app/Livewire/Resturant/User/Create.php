<?php

namespace App\Livewire\Resturant\User;

use App\Models\User;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Spatie\Permission\Models\Role;
use App\Traits\HasRolesAndPermissions;

class Create extends Component
{
    use HasRolesAndPermissions;
    public $name;
    public $email;
    public $role;
    public $mobile;
    public $password;
    public $password_confirmation;
    public $resturant;
    public $data = [
        'roles' => [],
    ];
    #[Layout('components.layouts.resturant.app')]
    public function render()
    {
        $this->resturant = auth()->user()->restaurants()->first();
        return view('livewire.resturant.user.create');
    }

    public function mount()
    {
        $this->data['roles'] = Role::whereIn('name', ['manager', 'waiter', 'kitchen'])->pluck('name', 'name');
    }

    public function submit()
    {
        $this->validate([
            'name' => ['required', 'min:2', 'max:50'],
            'email' => ['required', 'email'],
            'mobile' => ['required', 'numeric', 'digits:10'],
            'password' => ['required', 'min:6', 'max:20', 'confirmed'],
            'role' => ['required'],
        ]);

        $restaurantSuffix = preg_replace('/[^a-z0-9]/', '', strtolower($this->resturant->name));

        $emailLocalPart = explode('@', $this->email)[0];

        $finalEmail = $emailLocalPart . '@' . $restaurantSuffix . 'gmail.com';

        $user = User::create([
            'restaurant_id' => $this->resturant->id,
            'name' => $this->name,
            'email' => $finalEmail,
            'mobile' => $this->mobile,
            'password' => bcrypt($this->password),
        ]);

        $user->assignRole($this->role);

        $this->redirect(route('restaurant.users.index'));
    }

}
