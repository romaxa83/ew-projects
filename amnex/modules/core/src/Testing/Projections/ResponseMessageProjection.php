<?php

namespace Wezom\Core\Testing\Projections;

class ResponseMessageProjection extends Projection
{
    protected function fields(): array
    {
        return [
            GraphQLProjection::MESSAGE_FIELD,
            GraphQLProjection::TYPE_FIELD,
        ];
    }
}
