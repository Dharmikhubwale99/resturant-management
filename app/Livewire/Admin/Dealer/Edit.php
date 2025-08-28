<?php

namespace App\Livewire\Admin\Dealer;

use Livewire\Component;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Livewire\Attributes\Layout;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use App\Traits\HasRolesAndPermissions;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Str;

class Edit extends Component
{
    use HasRolesAndPermissions;


    public $dealer;


    public $username;
    public $name;
    public $email;
    public $password;
    public $password_confirmation;
    public $mobile;
    public $role;
    public $commission_rate;
    public $roles = [];
    public $permissions = [];

    public $data = [
        'roles' => [],
        'permissions' => [],
    ];

    #[Layout('components.layouts.admin.app')]
    public function render()
    {
        return view('livewire.admin.dealer.edit');
    }


    public function mount($id)
    {
        $this->dealer = User::findOrFail($id);
        $this->roles = Role::whereIn('name', ['dealer', 'superadmin'])
                ->pluck('name', 'name')
                ->toArray();

        $this->name     = $this->dealer->name;
        $this->email    = $this->dealer->email;
        $this->username = $this->dealer->username;
        $this->mobile   = $this->dealer->mobile;
        $this->commission_rate = $this->dealer->commission_rate;
        $this->role = $this->dealer->roles->pluck('name')->first();
        $this->permissions = $this->dealer->getPermissionNames()->toArray();


        $flat = collect($this->getSuperAdminPermissions())->values();
        $this->data['permissions'] = $flat
            ->groupBy(fn($p) => \Illuminate\Support\Str::before($p, '-'))
            ->map(fn($g) => $g->values()->all())
            ->sortKeys()
            ->toArray();

        $this->permissions = array_values(array_intersect(
            $this->permissions, $flat->all()
        ));

    }

    public function submit()
    {
        try {
            $this->validate([
                'name' => ['required', 'min:2', 'max:50'],
                'email' => ['required', 'email'],
                'mobile' => ['required', 'numeric', 'digits:10'],

                'username' => [
                    'required',
                    'min:6',
                    'max:50',
                    Rule::unique('users', 'username')->ignore($this->dealer->id),
                ],

                'password' => [
                    'nullable',
                    'min:6',
                    'max:20',
                    'confirmed',
                ],
                'role' => ['required'],
            ]);

            $permissions = is_string($this->permissions)
                ? array_filter(explode(',', $this->permissions))
                : (array) $this->permissions;


            $this->dealer->name     = $this->name;
            $this->dealer->email    = $this->email;
            $this->dealer->username = $this->username;
            $this->dealer->mobile   = $this->mobile;
            $this->dealer->commission_rate = $this->commission_rate;

            if (!empty($this->password)) {
                $this->dealer->password = Hash::make($this->password);
            }

            $this->dealer->save();


            $this->dealer->syncRoles([$this->role]);
            $this->dealer->syncPermissions($permissions);

            session()->flash('success', 'Dealer updated successfully!');
            return redirect()->to(route('superadmin.dealer.index'));
        } catch (\Throwable $e) {
            report($e);
            $this->addError('server', 'An error occurred: ' . $e->getMessage());
        }
    }
}
