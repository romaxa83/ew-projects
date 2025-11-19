<?php

declare(strict_types=1);

namespace Wezom\Core\Tests\GraphQL\Projections;

use Wezom\Core\Testing\Projections\Projection;

class BreadcrumbProjection extends Projection
{
    protected function fields(): array
    {
        return [
            'id',
            'parentId',
            'name',
            'slug',
        ];
    }
}
