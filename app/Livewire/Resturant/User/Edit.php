<?php

namespace App\Livewire\Resturant\User;

use App\Models\User;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Spatie\Permission\Models\Role;
use App\Traits\HasRolesAndPermissions;
use Spatie\Permission\Models\Permission;

class Edit extends Component
{
    use HasRolesAndPermissions;
    public $name;
    public $email;
    public $role;
    public $mobile;
    public $password;
    public $password_confirmation;
    public $restaurant;
    public $permissions = [];
    public array $roles = [];
    public $data = [
        'permissions' => [],
    ];

    public $user;
    #[Layout('components.layouts.resturant.app')]
    public function render()
    {
        return view('livewire.resturant.user.edit');
    }

    public function mount($id)
    {
        if (!setting('user')) {
            abort(403, 'You do not have access to this module.');
        }

        $this->restaurant = auth()->user()->restaurants()->first();
        $this->user = User::findOrFail($id);

        $this->roles = Role::whereIn('name', ['manager', 'waiter', 'kitchen'])
                           ->pluck('name', 'name')
                           ->toArray();

        $this->name = $this->user->name;
        $this->email = $this->user->email;
        $this->role = $this->user->roles->pluck('name')->first(); // role field
        $this->mobile = $this->user->mobile;

        $this->permissions = $this->user->permissions->pluck('name')->toArray();

        $allPermissions = $this->getAllPermissionGroups();

        $restaurantConfigIds = $this->restaurant
            ->configurations()
            ->where('value', 1)
            ->pluck('configuration_id')
            ->toArray();

        $filteredPermissions = [];
        foreach ($allPermissions as $group => $perms) {
            if (in_array($this->mapModuleToConfigId($group), $restaurantConfigIds)) {
                $filteredPermissions[$group] = $perms;
            }
        }

        $this->data['permissions'] = $filteredPermissions;
    }

    public function submit()
    {
        $this->validate([
            'name' => 'required',
            'email' => 'required|email',
            'role' => 'required',
            'mobile' => 'required|numeric|digits:10',
            'password' => ['nullable', 'min:6', 'max:20', 'confirmed'],
        ]);

        $data = [
            'restaurant_id' => $this->restaurant->id,
            'name' => $this->name,
            'email' => $this->email,
            'mobile' => $this->mobile,
        ];

        if ($this->password) {
            $data['password'] = bcrypt($this->password);
        }

        $this->user->update($data);
        $this->user->syncRoles([$this->role]);
        if (!empty($this->permissions)) {
            $permissionList = is_array($this->permissions)
                ? $this->permissions
                : explode(',', $this->permissions);

            $validPermissions = Permission::whereIn('name', $permissionList)
                ->pluck('name')
                ->toArray();

            $this->user->syncPermissions($validPermissions);
        }
        return redirect()->route('restaurant.users.index');
    }

}
