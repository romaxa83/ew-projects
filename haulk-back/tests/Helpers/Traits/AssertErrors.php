<?php

namespace Tests\Helpers\Traits;

use Illuminate\Testing\TestResponse;

trait AssertErrors
{
    protected function assertResponseHasValidationMessage(
        TestResponse $result,
        string $attribute,
        string $msg,
        ?int $code = null
    ): void
    {
        self::assertEquals($result->json('errors.0.source.parameter'), $attribute);
        self::assertEquals($result->json('errors.0.title'), $msg);

        if($code){
            self::assertEquals($result->json('errors.0.status'), $code);
        }
    }

    protected function assertResponseUnauthorizedMessage(TestResponse $result): void
    {
        self::assertEquals($result->json('errors.0.title'), "This action is unauthorized.");
        self::assertEquals($result->json('errors.0.status'), 403);
    }

    protected function assertResponseErrorMessage(TestResponse $result,string $msg, ?int $code = null): void
    {
        self::assertEquals($result->json('errors.0.title'), $msg);
        if($code){
            self::assertEquals($result->json('errors.0.status'), $code);
        }

    }
}
