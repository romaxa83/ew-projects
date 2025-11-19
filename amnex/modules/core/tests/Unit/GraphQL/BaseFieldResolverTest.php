<?php

declare(strict_types=1);

namespace Wezom\Core\Tests\Unit\GraphQL;

use PHPUnit\Framework\Attributes\Test;
use Validator;
use Wezom\Core\ExtendPackage\Exception\ValidationException;
use Wezom\Core\GraphQL\Context;
use Wezom\Core\Testing\TestCase;
use Wezom\Core\Tests\Unit\Source\TestingFieldResolver;
use Wezom\Core\Tests\Unit\Source\TestingService;

class BaseFieldResolverTest extends TestCase
{
    #[Test]
    public function resolveThrowValidationException(): void
    {
        $this->expectException(ValidationException::class);

        // Given
        $context = new Context(
            null,
            [],
            null,
            null,
        );

        $service = $this->mock(TestingService::class);
        $service->shouldReceive('createSomething')
            ->once()
            ->andThrow(
                new ValidationException(
                    'validation',
                    Validator::make([], [])
                )
            );

        // When
        config()->set('app.env', 'production');

        app(TestingFieldResolver::class)->resolve($context);
    }
}
