<?php

namespace App\Traits;

use App\Services\Telegram\TelegramDev;
use GraphQL\Error\Error;

trait GraphqlResponse
{
    public function successResponse(string $message, $code = 0): array
    {
        return [
            'code' => $code,
            'status' => true,
            'message' => $message
        ];
    }

    public function errorResponse(string $message, $code = 0): array
    {
        return [
            'code' => $code,
            'status' => false,
            'message' => $message
        ];
    }

    public function throwExceptionError(\Throwable $e, $code = null): void
    {
        \Log::error($e->getMessage());

        throw new Error(
            $e->getMessage(),
            null,
            null,
            null,
            null,
            null,
            ['code' => $code ?? $e->getCode()]
        );
    }
}
