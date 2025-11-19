<?php

declare(strict_types=1);

namespace Wezom\Core\Services;

use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Nuwave\Lighthouse\Exceptions\AuthenticationException;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class AuthenticationService
{
    public function authenticate(array|string $guards, GraphQLContext $context): void
    {
        $guards = array_wrap($guards);
        if (!$guards) {
            return;
        }

        $authFactory = app(AuthFactory::class);
        foreach ($guards as $guard) {
            $user = $authFactory->guard($guard)->user();

            if ($user !== null) {
                $authFactory->shouldUse($guard);

                $context->setUser($user);

                return;
            }
        }

        throw new AuthenticationException(AuthenticationException::MESSAGE, $guards);
    }
}
