<?php

namespace App\GraphQL\Mutations\BackOffice\Auth\Admin;

use App\Services\Admins\AdminService;
use Core\GraphQL\Mutations\BaseChangePasswordMutation;

class AdminChangePasswordMutation extends BaseChangePasswordMutation
{
    public const NAME = 'AdminChangePassword';

    public function __construct(protected AdminService $service)
    {
        $this->setAdminGuard();
    }
}
