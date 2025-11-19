<?php

declare(strict_types=1);

namespace Wezom\Core\Tests\Unit\GraphQL\Projections;

use Wezom\Core\Testing\Projections\TranslationProjection;

class SomeTranslationProjection extends TranslationProjection
{
    protected function fields(): array
    {
        return [
            'id',
            'translationField',
        ];
    }
}
