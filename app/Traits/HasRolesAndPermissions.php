<?php

namespace App\Traits;

trait HasRolesAndPermissions
{
    public function getAllRoles(){
        return [
            'superadmin',
            'admin',
            'manager',
            'waiter',
            'kitchen',
        ];
    }

    public function getAllPermissions(){
        return [

        ];
    }

    public function getAllPermissionGroups()
    {
        foreach ($this->getAllPermissions() as $permission) {
            $data[explode('-', $permission)[0]][$permission] = $permission;
        }

        return $data;
    }

    public function getAdminPermissions()
    {
        return [

        ];
    }
}
