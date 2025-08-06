<?php

namespace App\Livewire\Resturant\User;

use App\Models\User;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Spatie\Permission\Models\Role;
use App\Traits\HasRolesAndPermissions;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Permission;

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
    public $permissions = [];
    public $data = [
        'roles' => [],
        'permissions' => [],
    ];

    #[Layout('components.layouts.resturant.app')]
    public function render()
    {
        return view('livewire.resturant.user.create');
    }

    public function mount()
    {
        $this->resturant = auth()->user()->restaurants()->first();

        if (!setting('user')) {
            abort(403, 'You do not have access to this module.');
        }

        $allPermissions = $this->getAllPermissionGroups();

        $restaurantConfigIds = $this->resturant->configurations()->where('value', 1)->pluck('configuration_id')->toArray();

        $filteredPermissions = [];
        foreach ($allPermissions as $group => $perms) {
            if (in_array($this->mapModuleToConfigId($group), $restaurantConfigIds)) {
                $filteredPermissions[$group] = $perms;
            }
        }

        $this->data['permissions'] = $filteredPermissions;
        $this->data['roles'] = Role::whereIn('name', ['manager', 'waiter', 'kitchen'])->pluck('name', 'name');
    }

    public function submit()
    {
        try {
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

            if (!empty($this->permissions)) {
                $permissionList = is_array($this->permissions)
                    ? $this->permissions
                    : explode(',', $this->permissions);

                $validPermissions = Permission::whereIn('name', $permissionList)
                    ->pluck('name')
                    ->toArray();

                $user->syncPermissions($validPermissions);
            }

            session()->flash('success', 'User Created successfully!');
            return redirect()->to(route('restaurant.users.index'));
        } catch (\Throwable $e) {
            report($e);
            $this->addError('server', 'An error occurred: ' . $e->getMessage());
        }
    }
}
