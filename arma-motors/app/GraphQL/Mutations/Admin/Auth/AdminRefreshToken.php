<?php

namespace App\GraphQL\Mutations\Admin\Auth;

use App\GraphQL\BaseGraphQL;
use App\Services\Auth\AdminPassportService;
use App\Services\Telegram\TelegramDev;
use GraphQL\Error\Error;

class AdminRefreshToken extends BaseGraphQL
{
    public function __construct(
        protected AdminPassportService $passportService
    ){}

    /**
     * @param  null  $_
     * @param  array<string, mixed>  $args
     *
     * @throws Error
     *
     * @return array
     */
    public function __invoke($_, array $args)
    {
        try {
            $refreshToken = $args['refreshToken'];

            $tokens =  arrayKeyToCamel($this->passportService->refreshToken($refreshToken));

            if (isset($tokens['error'])) {
                throw new Error($tokens['errorDescription']);
            }

            return $tokens;
        } catch (\Throwable $e) {
            TelegramDev::error(__FILE__, $e, TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }
}
