<?php
namespace App\Services\Permissions\Templates;

use App\Models\Users\User;

class BodyShopSuperAdmin extends AbstractRole
{
    protected $roleName = User::BSSUPERADMIN_ROLE;

    protected $permissions = [
        'profile' => [
            'read',
            'update',
        ],
        'vehicle-owners' => [
            'read',
            'create',
            'update',
            'delete',
        ],
        'suppliers' => [
            'create',
            'read',
            'update',
            'delete',
        ],
        'inventory-categories' => [
            'create',
            'read',
            'update',
            'delete',
        ],
    ];
}
