<?php

namespace App\GraphQL\Mutations\FrontOffice\Users;

use App\Services\Users\UserService;
use Core\GraphQL\Mutations\BaseChangePasswordMutation;

class UserChangePasswordMutation extends BaseChangePasswordMutation
{
    public const NAME = 'userChangePassword';

    public function __construct(protected UserService $service)
    {
    }
}
