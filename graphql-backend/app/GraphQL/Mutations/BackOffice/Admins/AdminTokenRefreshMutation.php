<?php

namespace App\GraphQL\Mutations\BackOffice\Admins;

use App\GraphQL\Types\Admins\AdminLoginType;
use App\Services\Auth\AdminPassportService;
use Core\GraphQL\Mutations\BaseTokenRefreshMutation;
use GraphQL\Type\Definition\Type;

class AdminTokenRefreshMutation extends BaseTokenRefreshMutation
{
    public const NAME = 'adminRefreshToken';

    public function __construct(
        protected AdminPassportService $passportService
    ) {
    }

    public function type(): Type
    {
        return AdminLoginType::type();
    }
}
