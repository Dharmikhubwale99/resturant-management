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

    protected function mapModuleToConfigId($moduleName)
    {
        $mapping = [
            'category' => 1,
            'area' => 2,
            'expensetype' => 3,
            'user' => 4,
            'item' => 5,
            'expenses' => 6,
            'table' => 7,
            'discount' => 8,
            'kitchen' => 9,
        ];

        return $mapping[strtolower($moduleName)] ?? null;
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
        if (!empty($this->permissions)) {
            $user->syncPermissions($this->permissions);
        }

        $this->redirect(route('restaurant.users.index'));
    }
}
