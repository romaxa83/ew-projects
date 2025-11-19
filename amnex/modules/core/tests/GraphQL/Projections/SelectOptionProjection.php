<?php

declare(strict_types=1);

namespace Wezom\Core\Tests\GraphQL\Projections;

use Wezom\Core\Testing\Projections\Projection;

class SelectOptionProjection extends Projection
{
    protected function fields(): array
    {
        return [
            'id',
            'depth',
            'hasChildren',
            'name',
            'disabled',
        ];
    }
}
