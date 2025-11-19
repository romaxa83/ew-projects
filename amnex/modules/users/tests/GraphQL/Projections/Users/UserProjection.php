<?php

declare(strict_types=1);

namespace Wezom\Users\Tests\GraphQL\Projections\Users;

use Wezom\Core\Testing\Projections\Projection;

class UserProjection extends Projection
{
    protected function fields(): array
    {
        return [
            'id',
            'firstName',
            'lastName',
            'email',
            'emailVerifiedAt',
        ];
    }
}
