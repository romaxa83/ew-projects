<?php

declare(strict_types=1);

namespace Wezom\Core\ExtendPackage\Macro;

use Illuminate\Testing\Assert;
use Illuminate\Testing\TestResponse;
use Wezom\Core\Enums\Messages\MessageTypeEnum;

class TestResponseMacro
{
    public static function register(): void
    {
        self::assertNoErrors();
        self::assertHasValidationMessage();
        self::assertSuccessResponseMessage();
        self::assertWarningResponseMessage();
        self::assertFailedResponseMessage();
        self::assertHasErrorMessage();
    }

    private static function assertNoErrors(): void
    {
        /**
         * @return \Illuminate\Testing\TestResponse
         */
        TestResponse::macro('assertNoErrors', function () {
            $this->assertOk();

            Assert::assertEmpty($this->json('errors'), 'Response has errors.');

            return $this;
        });
    }

    private static function assertHasValidationMessage(): void
    {
        /**
         * @return \Illuminate\Testing\TestResponse
         */
        TestResponse::macro('assertHasValidationMessage', function (string $attribute, string|array $messages) {
            if (is_string($messages)) {
                $messages = [$messages];
            }

            $messages = array_map(
                fn ($message) => str_replace(':attribute', str_replace('_', ' ', snake_case($attribute)), $message),
                $messages
            );

            Assert::assertNotNull($this->json('errors'), 'Response doesnt have errors');
            $validationMessages = $this->json('errors.0.extensions.validation')[$attribute];

            Assert::assertTrue(count($validationMessages) > 0);
            Assert::assertTrue(count($messages) > 0);

            foreach ($messages as $message) {
                $validationMessage = array_shift($validationMessages);
                Assert::assertEquals($message, $validationMessage);
            }

            return $this;
        });
    }

    private static function assertSuccessResponseMessage(): void
    {
        /**
         * @return \Illuminate\Testing\TestResponse
         */
        TestResponse::macro('assertSuccessResponseMessage', function (?string $message = null) {
            /** @var TestResponse $that */
            $that = $this;

            $that->assertNoErrors()
                ->assertJsonFragment(
                    [
                        'message' => $message ?? __('core::messages.action.success'),
                        'type' => MessageTypeEnum::SUCCESS->value,
                    ],
                );

            return $that;
        });
    }

    private static function assertWarningResponseMessage(): void
    {
        /**
         * @return \Illuminate\Testing\TestResponse
         */
        TestResponse::macro('assertWarningResponseMessage', function (?string $message = null) {
            /** @var TestResponse $that */
            $that = $this;

            $that->assertNoErrors()
                ->assertJsonFragment(
                    [
                        'message' => $message ?? __('core::messages.action.warning'),
                        'type' => MessageTypeEnum::WARNING->value,
                    ],
                );

            return $that;
        });
    }

    private static function assertFailedResponseMessage(): void
    {
        /**
         * @return \Illuminate\Testing\TestResponse
         */
        TestResponse::macro('assertFailResponseMessage', function (?string $message = null) {
            /** @var TestResponse $that */
            $that = $this;

            $that->assertNoErrors()
                ->assertJsonFragment(
                    [
                        'message' => $message ?? __('core::messages.action.fail'),
                        'type' => MessageTypeEnum::DANGER->value,
                    ],
                );

            return $that;
        });
    }

    private static function assertHasErrorMessage(): void
    {
        /**
         * @return \Illuminate\Testing\TestResponse
         */
        TestResponse::macro('assertHasErrorMessage', function (string $message, ?string $category = null) {
            Assert::assertNotNull($this->json('errors'), 'Response doesnt have errors');
            Assert::assertSame($message, $this->json('errors.0.message'), 'Response doesnt have errors');

            if ($category) {
                Assert::assertSame(
                    $category,
                    $this->json('errors.0.extensions.category'),
                    "Response does not have expected category: $category"
                );
            }

            return $this;
        });
    }
}
