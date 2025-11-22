<?php

namespace App\GraphQL\Mutations\FrontOffice\Users;

use App\GraphQL\Types\Users\UserLoginType;
use App\Services\Auth\UserPassportService;
use Core\GraphQL\Mutations\BaseTokenRefreshMutation;
use GraphQL\Type\Definition\Type;

class UserTokenRefreshMutation extends BaseTokenRefreshMutation
{
    public const NAME = 'userRefreshToken';

    public function __construct(
        protected UserPassportService $passportService
    ) {
    }

    public function type(): Type
    {
        return UserLoginType::type();
    }
}
