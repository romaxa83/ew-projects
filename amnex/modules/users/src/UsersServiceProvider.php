<?php

declare(strict_types=1);

namespace Wezom\Users;

use Wezom\Core\BaseServiceProvider;
use Wezom\Users\Dto\UserRegistrationDto;

class UsersServiceProvider extends BaseServiceProvider
{
    protected array $graphQlInputs = [
        UserRegistrationDto::class,
    ];
}
