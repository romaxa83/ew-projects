<?php


namespace App\Services\Permissions\Templates;


use App\Models\Users\User;

class Admin extends AbstractRole
{
    protected $roleName = User::ADMIN_ROLE;

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
        'library' => [
            'read',
            'create',
            'delete',
        ],
        'question-answer' => [
            'create',
            'read',
            'update',
            'delete',
        ],
        'vehicle-owners' => [
            'create',
            'update',
            'delete',
            'read',
        ],
        'gps-devices' => [
            'read',
            'create',
            'update',
            'attach_to_vehicle',
            'list',
            'request',
        ],
        'gps' => [
            'read',
            'alerts',
            'devices',
            'subscription',
        ]
    ];
}
