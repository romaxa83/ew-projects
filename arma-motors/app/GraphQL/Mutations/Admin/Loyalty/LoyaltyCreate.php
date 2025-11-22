<?php

namespace App\GraphQL\Mutations\Admin\Loyalty;

use App\DTO\User\LoyaltyDTO;
use App\GraphQL\BaseGraphQL;
use App\Models\Admin\Admin;
use App\Models\User\Loyalty\Loyalty;
use App\Services\Telegram\TelegramDev;
use App\Services\User\LoyaltyService;
use GraphQL\Error\Error;

class LoyaltyCreate extends BaseGraphQL
{
    public function __construct(
        protected LoyaltyService $service,
    )
    {}

    /**
     * @param  null  $_
     * @param  array<string, mixed>  $args
     *
     * @throws Error
     *
     * @return Loyalty
     */
    public function __invoke($_, array $args): Loyalty
    {
        $user = \Auth::guard(Admin::GUARD)->user();
        try {

            $model = $this->service->create(
                LoyaltyDTO::byArgs($args)
            );

            // @todo dev-telegram
            TelegramDev::info("Создана программа лояльности", $user->name);

            return $model;
        } catch (\Throwable $e) {
            TelegramDev::error(__FILE__, $e, $user->name, TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }
}
