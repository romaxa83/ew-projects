<?php

namespace App\GraphQL\Mutations\FrontOffice\Technicians;

use App\GraphQL\Types\Members\MemberLoginType;
use App\Services\Auth\TechnicianPassportService;
use Core\GraphQL\Mutations\BaseTokenRefreshMutation;
use GraphQL\Type\Definition\Type;

class TechnicianTokenRefreshMutation extends BaseTokenRefreshMutation
{
    public const NAME = 'technicianRefreshToken';

    public function __construct(
        protected TechnicianPassportService $passportService
    ) {
        $this->setTechnicianGuard();
    }

    public function type(): Type
    {
        return MemberLoginType::type();
    }
}
