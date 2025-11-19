<?php

declare(strict_types=1);

namespace Wezom\Core\Tests\Unit\GraphQL\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Wezom\Core\Traits\Factory\HasTranslationFactoryTrait;

class SomeFactory extends Factory
{
    use HasTranslationFactoryTrait;

    public function definition(): array
    {
        return [];
    }
}
