<?php

declare(strict_types=1);

namespace Wezom\Core\Tests\Unit\GraphQL\Projections;

use Wezom\Core\Testing\Projections\Projection;

class SomeProjection extends Projection
{
    protected function fields(): array
    {
        return [
            'id',
            'name',
        ];
    }
}
