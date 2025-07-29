<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Traits\HasRolesAndPermissions;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    use HasRolesAndPermissions;

    public function run(): void
    {
        $roles = $this->getAllRoles();

        foreach ($roles as $role) {
            Role::firstOrCreate([
                'name' => $role,
            ],
            [
                'name' => $role,
            ]);
        }

        $permissions = array_merge($this->getAllPermissions(), $this->getAdminPermissions());

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
            ], [
                'name' => $permission,
            ]);
        }
    }
}
