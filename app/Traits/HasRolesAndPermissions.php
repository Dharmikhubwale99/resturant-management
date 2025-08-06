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

            'order',

            'party-index',
            'party-create',

            'moneyin-index',
            'moneyin-create',

            'moneyout-index',
            'moneyout-create',

            'report-index',
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
            'order' => 10,
            'report' => 11,
            'moneyin' => 12,
            'moneyout' => 13,
            'party' => 14,
        ];

        return $mapping[strtolower($moduleName)] ?? null;
    }
}
