<?php

namespace App\GraphQL\Mutations\BackOffice\Admins;

use App\GraphQL\Types\Admins\AdminLoginType;
use App\Services\Auth\AdminPassportService;
use Core\GraphQL\Mutations\BaseLoginMutation;
use GraphQL\Type\Definition\Type;

class AdminLoginMutation extends BaseLoginMutation
{
    public const NAME = 'adminLogin';

    public function __construct(
        protected AdminPassportService $passportService
    ) {
        $this->setAdminGuard();
    }

    public function type(): Type
    {
        return AdminLoginType::type();
    }

}
