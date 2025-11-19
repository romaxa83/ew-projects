<?php

namespace Wezom\Core\Tests\Feature\ErrorHandler;

use Illuminate\Testing\TestResponse;
use Mockery\MockInterface;
use RuntimeException;
use Wezom\Core\Enums\GraphQLErrorClassification;
use Wezom\Core\Exceptions\TranslatedException;
use Wezom\Core\Services\TranslationService;
use Wezom\Core\Tests\Feature\TranslationTestAbstract;

class HandleClientAwareExceptionTest extends TranslationTestAbstract
{
    public function setUp(): void
    {
        parent::setUp();

        $this->loginAsAdminWithPermissions(['translations.create']);
    }

    protected function operationName(): string
    {
        return 'backTranslationCreate';
    }

    protected function createRequest(array $translation): TestResponse
    {
        return $this->mutation($this->operationName())
            ->args(compact('translation'))
            ->select([
                'id',
                'key',
                'language',
                'text',
                'side',
            ])->executeAndReturnResponse();
    }

    public function testHandleGeneralException(): void
    {
        $attrs = $this->attrs();

        $this->mock(
            TranslationService::class,
            fn (MockInterface $mock) => $mock->shouldReceive('create')
                ->withAnyArgs()
                ->andThrow(new RuntimeException('Test error message'))
        );

        $response = $this->createRequest($attrs)
            ->assertOk();

        $this->assertGraphQlErrorMessage($response, __('core::exceptions.Something went wrong'));
        $this->assertGraphQlErrorClassification($response, GraphQLErrorClassification::INTERNAL_ERROR);
    }

    public function testHandleSafeException(): void
    {
        $attrs = $this->attrs();

        $expectedErrorMessage = 'Test error message';

        $this->mock(
            TranslationService::class,
            fn (MockInterface $mock) => $mock->shouldReceive('create')
                ->withAnyArgs()
                ->andThrow(new TranslatedException($expectedErrorMessage))
        );

        $response = $this->createRequest($attrs)
            ->assertOk();

        $this->assertGraphQlErrorMessage($response, $expectedErrorMessage);
        $this->assertGraphQlErrorClassification($response, GraphQLErrorClassification::INTERNAL_ERROR);
    }
}
