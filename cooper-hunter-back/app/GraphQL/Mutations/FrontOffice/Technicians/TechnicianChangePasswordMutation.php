<?php

namespace App\GraphQL\Mutations\FrontOffice\Technicians;

use App\Services\Technicians\TechnicianService;
use Core\GraphQL\Mutations\BaseChangePasswordMutation;

class TechnicianChangePasswordMutation extends BaseChangePasswordMutation
{
    public const NAME = 'technicianChangePassword';

    public function __construct(protected TechnicianService $service)
    {
        $this->setTechnicianGuard();
    }
}
