<?php

declare(strict_types=1);

namespace Wezom\Core\Tests\Feature\Queries\Testing;

use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\UnauthorizedException;
use PHPUnit\Framework\Attributes\Test;
use Wezom\Admins\Testing\TestCase;
use Wezom\Core\Enums\GraphQLErrorClassification;
use Wezom\Core\Exceptions\TranslatedException;
use Wezom\Core\ExtendPackage\Exception\ValidationException;
use Wezom\Core\GraphQL\Queries\Testing\TestingException;
use Wezom\Core\Services\TestingExceptionService;

class TestingExceptionQueryTest extends TestCase
{
    #[Test]
    public function throwBadRequestException(): void
    {
        $service = $this->mock(TestingExceptionService::class);
        $service->shouldReceive('createSomething')
            ->once()
            ->andThrow(
                new ValidationException(
                    GraphQLErrorClassification::BAD_REQUEST->name,
                    Validator::make([], [])
                )
            );

        $result = $this->query(TestingException::getName())
            ->executeAndReturnResponse();

        $this->assertGraphQlErrorMessage($result, GraphQLErrorClassification::BAD_REQUEST->name);
        $this->assertGraphQlErrorClassification($result, GraphQLErrorClassification::BAD_REQUEST);
    }

    #[Test]
    public function throwUnauthorizedException(): void
    {
        $service = $this->mock(TestingExceptionService::class);
        $service->shouldReceive('createSomething')
            ->once()
            ->andThrow(
                new AuthenticationException('Unauthenticated')
            );

        $result = $this->query(TestingException::getName())
            ->executeAndReturnResponse();

        $this->assertGraphQlErrorMessage($result, 'Unauthenticated');
        $this->assertGraphQlErrorClassification($result, GraphQLErrorClassification::UNAUTHORIZED);
    }

    #[Test]
    public function throwForbiddenAuthorizationException(): void
    {
        $service = $this->mock(TestingExceptionService::class);
        $service->shouldReceive('createSomething')
            ->once()
            ->andThrow(
                new AuthorizationException('Access Denied')
            );

        $result = $this->query(TestingException::getName())
            ->executeAndReturnResponse();

        $this->assertGraphQlErrorMessage($result, 'Access Denied');
        $this->assertGraphQlErrorClassification($result, GraphQLErrorClassification::FORBIDDEN);
    }

    #[Test]
    public function throwForbiddenUnauthorizedException(): void
    {
        $service = $this->mock(TestingExceptionService::class);
        $service->shouldReceive('createSomething')
            ->once()
            ->andThrow(
                new UnauthorizedException('Access Denied')
            );

        $result = $this->query(TestingException::getName())
            ->executeAndReturnResponse();

        $this->assertGraphQlErrorMessage($result, 'Access Denied');
        $this->assertGraphQlErrorClassification($result, GraphQLErrorClassification::FORBIDDEN);
    }

    #[Test]
    public function throwNotFoundException(): void
    {
        $service = $this->mock(TestingExceptionService::class);
        $service->shouldReceive('createSomething')
            ->once()
            ->andThrow(
                new ModelNotFoundException("Entity 'TagEntity' not found by id '123'")
            );

        $result = $this->query(TestingException::getName())
            ->executeAndReturnResponse();

        $this->assertGraphQlErrorMessage($result, "Entity 'TagEntity' not found by id '123'");
        $this->assertGraphQlErrorClassification($result, GraphQLErrorClassification::NOT_FOUND);
    }

    #[Test]
    public function throwInternalException(): void
    {
        $service = $this->mock(TestingExceptionService::class);
        $service->shouldReceive('createSomething')
            ->once()
            ->andThrow(
                new Exception('Error')
            );

        $result = $this->query(TestingException::getName())
            ->executeAndReturnResponse();

        $this->assertGraphQlErrorMessage($result, __('core::exceptions.Something went wrong'));
        $this->assertGraphQlErrorClassification($result, GraphQLErrorClassification::INTERNAL_ERROR);
    }

    #[Test]
    public function throwTranslatedException(): void
    {
        $service = $this->mock(TestingExceptionService::class);
        $service->shouldReceive('createSomething')
            ->once()
            ->andThrow(
                new TranslatedException('Message for user')
            );

        $result = $this->query(TestingException::getName())
            ->executeAndReturnResponse();

        $this->assertGraphQlErrorMessage($result, 'Message for user');
        $this->assertGraphQlErrorClassification($result, GraphQLErrorClassification::INTERNAL_ERROR);
    }
}
