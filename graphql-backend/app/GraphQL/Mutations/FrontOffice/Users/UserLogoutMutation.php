<?php

namespace App\GraphQL\Mutations\FrontOffice\Users;

use App\Services\Auth\UserPassportService;
use Core\GraphQL\Mutations\BaseLogoutMutation;

class UserLogoutMutation extends BaseLogoutMutation
{
    public const NAME = 'userLogout';

    public function __construct(
        protected UserPassportService $passportService
    ) {
    }
}
