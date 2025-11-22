<?php

namespace App\GraphQL\Mutations\FrontOffice\Users;

use App\GraphQL\Types\Users\UserLoginType;
use App\Rules\LoginUser;
use App\Services\Auth\UserPassportService;
use Core\GraphQL\Mutations\BaseLoginMutation;
use GraphQL\Type\Definition\Type;

class UserLoginMutation extends BaseLoginMutation
{
    public const NAME = 'userLogin';

    public function __construct(
        protected UserPassportService $passportService
    ) {
    }

    public function type(): Type
    {
        return UserLoginType::type();
    }

    protected function rules(array $args = []): array
    {
        $rules = parent::rules($args);

        $rules['password'] = ['required', 'string', 'min:8', new LoginUser($args)];

        return $rules;
    }
}
