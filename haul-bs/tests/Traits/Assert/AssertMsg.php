<?php

namespace Tests\Traits\Assert;

use Illuminate\Http\Response;
use Illuminate\Testing\TestResponse;

trait AssertMsg
{
    protected static function assertSimpleMsg(
        TestResponse $result,
        string $msg,
        int $code = Response::HTTP_OK,

    ): void
    {
        self::assertEquals($result->json('data.message'), $msg);
        self::assertEquals($result->status(), $code);
    }

    protected static function assertSuccessMsg(TestResponse $result): void
    {
        self::assertEquals($result->json('data.message'), "Success");
        self::assertEquals($result->status(), Response::HTTP_OK);
    }
}


