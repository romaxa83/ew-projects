<?php

declare(strict_types=1);

namespace Wezom\Core\Testing\Projections;

class SeoProjection extends Projection
{
    protected function fields(): array
    {
        return [
            GraphQLProjection::SEO_H1_FIELD,
            GraphQLProjection::SEO_TITLE_FIELD,
            GraphQLProjection::SEO_DESCRIPTION_FIELD,
        ];
    }
}
