<?php

declare(strict_types=1);

namespace Wezom\Core\Testing\Projections;

abstract class TranslationProjection extends Projection
{
    protected ?string $rootName = 'translation';
}
