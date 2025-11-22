<?php


namespace App\Services\Permissions\Templates;


use App\Models\Users\User;

class SuperDriver extends AbstractRole
{
    protected $roleName = User::SUPERDRIVER_ROLE;

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
        'library' => [
            'read',
            'create',
            'delete',
        ],
    ];
}
