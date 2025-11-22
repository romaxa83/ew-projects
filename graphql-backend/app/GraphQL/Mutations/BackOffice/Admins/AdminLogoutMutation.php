<?php

namespace App\GraphQL\Mutations\BackOffice\Admins;

use App\Services\Auth\AdminPassportService;
use Core\GraphQL\Mutations\BaseLogoutMutation;

class AdminLogoutMutation extends BaseLogoutMutation
{
    public const NAME = 'adminLogout';

    public function __construct(
        protected AdminPassportService $passportService
    ) {
        $this->setAdminGuard();
    }
}
