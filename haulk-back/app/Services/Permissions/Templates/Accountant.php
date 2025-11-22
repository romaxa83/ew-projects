<?php


namespace App\Services\Permissions\Templates;


use App\Models\Users\User;

class Accountant extends AbstractRole
{
    protected $roleName = User::ACCOUNTANT_ROLE;

    protected $permissions = [
        'profile' => [
            'read',
            'update',
        ],
        'orders' => [
            'create',
            'read',
            'update',
            'delete',
            'export',
        ],
        'contacts' => [
            'create',
            'read',
            'update',
            'delete',
        ],
        'company-reports' => [
            'read',
        ],
        'vehicle-owners' => [
            'read',
        ],
    ];
}
