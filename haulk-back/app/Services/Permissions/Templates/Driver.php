<?php


namespace App\Services\Permissions\Templates;


use App\Models\Users\User;

class Driver extends AbstractRole
{
    protected $roleName = User::DRIVER_ROLE;

    protected $permissions = [
        'profile' => [
            'read',
            'update',
        ],
        'orders' => [
            'create',
            'read',
            'update',
            'send-invoice',
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
    ];
}
