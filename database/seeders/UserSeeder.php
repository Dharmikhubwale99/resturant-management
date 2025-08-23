<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Traits\HasRolesAndPermissions;

class UserSeeder extends Seeder
{
    use HasRolesAndPermissions;
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $admin = User::firstOrCreate([
            'name' => 'Super Admin',
            'email' => 'admin@email.com',
            'username' => 'superadmin10',
            'mobile' => '1234567890'
        ],
        [
            'name' => 'Super Admin',
            'username' => 'superadmin10',
            'email' => 'admin@email.com',
            'password' => bcrypt('password'),
            'mobile' => '1234567890',
            'is_active' => 0,
        ]);

        $admin->assignRole('superadmin');
        $admin->syncPermissions(array_merge($this->getAllPermissions(), $this->getSuperAdminPermissions()));

    }
}
