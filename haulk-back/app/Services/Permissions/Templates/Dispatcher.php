<?php


namespace App\Services\Permissions\Templates;


use App\Models\Users\User;

class Dispatcher extends AbstractRole
{
    protected $roleName = User::DISPATCHER_ROLE;

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
        'question-answer' => [
            'read',
        ],
        'gps-devices' => [
            'read',
            'list',
        ],
        'gps' => [
            'read',
            'alerts',
            'devices',
            'subscription',
        ]
    ];
}
