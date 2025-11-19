<?php

declare(strict_types=1);

namespace Wezom\Core\Testing\Projections;

class SeoAndTextProjection extends Projection
{
    protected function fields(): array
    {
        return [
            GraphQLProjection::SEO_H1_FIELD,
            GraphQLProjection::SEO_TITLE_FIELD,
            GraphQLProjection::SEO_DESCRIPTION_FIELD,
            GraphQLProjection::SEO_TEXT_FIELD,
        ];
    }
}
