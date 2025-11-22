<?php

namespace App\GraphQL\Mutations\FrontOffice\Users;

use App\GraphQL\Types\Users\UserLoginType;
use App\Rules\LoginUser;
use App\Services\Auth\UserPassportService;
use Core\GraphQL\Mutations\BaseLoginMutation;
use GraphQL\Type\Definition\Type;
use Illuminate\Contracts\Validation\Rule;

class UserLoginMutation extends BaseLoginMutation
{
    public const NAME = 'userLogin';

    public function __construct(protected UserPassportService $passportService)
    {
        $this->setUserGuard();
    }

    public function type(): Type
    {
        return UserLoginType::nonNullType();
    }

    protected function loginRule(array $args): Rule
    {
        return new LoginUser($args);
    }
}
