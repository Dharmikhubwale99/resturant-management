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
            'user-index',
            'user-create',
            'user-edit',
            'user-delete',

            'category-index',
            'category-create',
            'category-edit',
            'category-delete',

            'item-index',
            'item-create',
            'item-edit',
            'item-delete',
            'item-show',
            'item-import',

            'area-index',
            'area-create',
            'area-edit',
            'area-delete',

            'table-index',
            'table-create',
            'table-edit',
            'table-delete',
            'table-show',

            'expensetype-index',
            'expensetype-create',
            'expensetype-edit',
            'expensetype-delete',

            'expenses-index',
            'expenses-create',
            'expenses-edit',
            'expenses-delete',
            'expenses-show',

            'discount-index',
            'discount-create',
            'discount-edit',
            'discount-delete',
            'discount-active',

            'kitchen-dashboard',
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
