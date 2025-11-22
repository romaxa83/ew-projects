<?php

namespace Tests\Traits\Assert;

use Illuminate\Http\Response;
use Illuminate\Testing\TestResponse;

trait AssertErrors
{
    protected static function assertUnauthenticatedMessage(
        TestResponse $result
    ): void
    {
        self::assertEquals($result->json('errors.0.title'), "Unauthenticated.");
        self::assertEquals($result->json('errors.0.status'), Response::HTTP_UNAUTHORIZED);
    }

    protected static function assertForbiddenMessage(
        TestResponse $result
    ): void
    {
        self::assertEquals($result->json('errors.0.title'), "This action is unauthorized.");
        self::assertEquals($result->json('errors.0.status'), Response::HTTP_FORBIDDEN);
    }


    protected static function assertForbiddenMessageAsReal(
        TestResponse $result
    ): void
    {
        self::assertEquals($result->json('errors.0.title'), "Forbidden");
        self::assertEquals($result->json('errors.0.status'), Response::HTTP_FORBIDDEN);
    }

    protected static function assertErrorMsg(
        TestResponse $result,
        string $msg,
        int $code = null
    ): void
    {
        self::assertEquals($result->json('errors.0.title'), $msg);
        if($code){
            self::assertEquals($result->json('errors.0.status'), $code);
        }
    }

    protected static function assertValidationMsg(
        TestResponse $result,
        string $msg,
        string $parameter
    ): void
    {
        self::assertEquals($result->json('errors.0.source.parameter'), $parameter);
        self::assertEquals($result->json('errors.0.title'), $msg);
        self::assertEquals($result->json('errors.0.status'), Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    protected static function assertAndTransformValidationMsg(
        TestResponse $res,
        string $msgKey,
        string $field,
        array $attributes = []
    ): void
    {
        $attr = [];
        foreach ($attributes as $k => $v){
            if(is_numeric($v)){
                $attr[$k] = $v;
            } else {
                $attr[$k] = __($v);
            }
        }

        self::assertValidationMsg($res, __($msgKey, $attr), $field);
    }

    protected static function assertValidationMsgWithValidateOnly(
        TestResponse $result,
        string $msg,
        string $parameter
    ): void
    {
        self::assertEquals($result->json('data.0.source.parameter'), $parameter);
        self::assertEquals($result->json('data.0.title'), $msg);
        self::assertEquals($result->json('data.0.status'), Response::HTTP_OK);
    }

}

