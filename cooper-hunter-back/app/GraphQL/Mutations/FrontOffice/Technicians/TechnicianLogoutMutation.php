<?php

namespace App\GraphQL\Mutations\FrontOffice\Technicians;

use App\Services\Auth\TechnicianPassportService;
use Core\GraphQL\Mutations\BaseLogoutMutation;

class TechnicianLogoutMutation extends BaseLogoutMutation
{
    public const NAME = 'technicianLogout';

    public function __construct(
        protected TechnicianPassportService $passportService
    ) {
        $this->setTechnicianGuard();
    }
}
