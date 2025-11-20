<?php

namespace App\GraphQL\Mutations\BackOffice\Auth\Admin;

use App\Services\Auth\AdminPassportService;
use Core\GraphQL\Mutations\BaseLogoutMutation;
use Core\Services\Auth\AuthPassportService;

class AdminLogoutMutation extends BaseLogoutMutation
{
    public const NAME = 'AdminLogout';

    public function __construct(
        protected AdminPassportService $passportService
    ) {
        $this->setAdminGuard();
    }

    protected function getPassportService(): AuthPassportService
    {
        return $this->passportService;
    }
}
