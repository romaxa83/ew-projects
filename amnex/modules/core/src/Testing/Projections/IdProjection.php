<?php

declare(strict_types=1);

namespace Wezom\Core\Testing\Projections;

class IdProjection extends Projection
{
    protected function fields(): array
    {
        return [
            GraphQLProjection::ID_FIELD,
        ];
    }
}
