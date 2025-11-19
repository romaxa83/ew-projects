<?php

declare(strict_types=1);

namespace Wezom\Core\Tests\Unit\GraphQL\Factories;

use PHPUnit\Framework\Attributes\Test;
use Wezom\Core\Exceptions\Factory\TranslationFactoryNotFoundException;
use Wezom\Core\Testing\TestCase;

class TranslationFactoriesResolverTest extends TestCase
{
    #[Test]
    public function resolveWhenNotTranslationFactoryNotExists(): void
    {
        $factory = SomeFactory::new();

        $translationFactoryName = $factory->guessTranslationModelFactoryName();

        $this->assertEquals('Wezom\Core\Tests\Unit\GraphQL\Factories\SomeTranslationFactory', $translationFactoryName);

        $this->expectException(TranslationFactoryNotFoundException::class);
        $this->expectExceptionMessage("Factory [$translationFactoryName] does not exist.");

        $factory->resolveTranslationFactory();
    }
}
