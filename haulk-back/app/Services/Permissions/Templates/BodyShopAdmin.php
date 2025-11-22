<?php
namespace App\Services\Permissions\Templates;

use App\Models\Users\User;

class BodyShopAdmin extends AbstractRole
{
    protected $roleName = User::BSADMIN_ROLE;

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
