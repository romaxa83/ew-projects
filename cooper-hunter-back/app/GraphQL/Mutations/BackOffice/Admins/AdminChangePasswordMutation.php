<?php

namespace App\GraphQL\Mutations\BackOffice\Admins;

use App\Services\Admins\AdminService;
use Core\GraphQL\Mutations\BaseChangePasswordMutation;

class AdminChangePasswordMutation extends BaseChangePasswordMutation
{
    public const NAME = 'adminChangePassword';

    public function __construct(protected AdminService $service)
    {
        $this->setAdminGuard();
    }
}
