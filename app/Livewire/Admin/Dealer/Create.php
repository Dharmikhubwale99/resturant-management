<?php

namespace App\Livewire\Admin\Dealer;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\User;
use Livewire\WithFileUploads;
use App\Traits\HasRolesAndPermissions;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Str;

class Create extends Component
{
    use WithFileUploads, HasRolesAndPermissions;

    public $username;
    public $name;
    public $email;
    public $password;
    public $password_confirmation;
    public $mobile;
    public $role;
    public $permissions = [];
    public $data = [
        'roles' => [],
        'permissions' => [],
    ];

    #[Layout('components.layouts.admin.app')]
    public function render()
    {
        return view('livewire.admin.dealer.create');
    }

    public function mount()
    {

        $this->data['roles'] = Role::whereIn('name', ['dealer', 'superadmin'])
            ->pluck('name', 'name')
            ->toArray();


        $flat = collect($this->getSuperAdminPermissions())->filter(fn($p) => is_string($p))->unique()->values();


        $this->data['permissions'] = $flat
            ->groupBy(fn($p) => \Illuminate\Support\Str::before($p, '-'))
            ->map(fn($group) => $group->values()->all())
            ->sortKeys()
            ->toArray();
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
                'username' => ['required', 'min:6', 'max:50', 'unique:users,username'],
            ]);


            $permissions = is_string($this->permissions) ? array_filter(explode(',', $this->permissions)) : (array) $this->permissions;

            $user = User::create([
                'name' => $this->name,
                'email' => $this->email,
                'username' => $this->username,
                'mobile' => $this->mobile,
                'password' => bcrypt($this->password),
            ]);

            $user->assignRole($this->role);
            $user->syncPermissions($permissions);

            session()->flash('success', 'Dealer Created successfully!');
            return redirect()->to(route('superadmin.dealer.index'));
        } catch (\Throwable $e) {
            report($e);
            $this->addError('server', 'An error occurred: ' . $e->getMessage());
        }
    }
}
