<?php

declare(strict_types=1);

namespace Wezom\Core\Testing\Projections;

class FileProjection extends Projection
{
    protected function fields(): array
    {
        return [
            GraphQLProjection::ID_FIELD,
            GraphQLProjection::NAME_FIELD,
            GraphQLProjection::MIME_TYPE_FIELD,
            GraphQLProjection::SIZE_FIELD,
            GraphQLProjection::ORIGINAL_URL_FIELD,
            GraphQLProjection::CONVERSIONS_FIELD => [
                GraphQLProjection::SIZE_FIELD,
                GraphQLProjection::URL_FIELD,
            ],
        ];
    }
}
