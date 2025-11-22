<?php

namespace App\GraphQL\Mutations\Catalog\Car;

use App\DTO\Catalog\Car\FuelDTO;
use App\GraphQL\BaseGraphQL;
use App\Models\Admin\Admin;
use App\Models\Catalogs\Car\Fuel;
use App\Services\Catalog\Car\FuelService;
use App\Services\Telegram\TelegramDev;
use GraphQL\Error\Error;

class FuelCreate extends BaseGraphQL
{
    public function __construct(
        protected FuelService $service,
    )
    {}

    /**
     * @param  null  $_
     * @param  array<string, mixed>  $args
     *
     * @throws Error
     *
     * @return Fuel
     */
    public function __invoke($_, array $args): Fuel
    {
        $user = \Auth::guard(Admin::GUARD)->user();
        try {
            $model = $this->service->create(
                FuelDTO::byArgs($args)
            );

            // @todo dev-telegram
            TelegramDev::info("Тип топлива ({$model->current->name}) СОЗДАН", $user->name);

            return $model;
        } catch (\Throwable $e) {
            TelegramDev::error(__FILE__, $e, $user->name, TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }
}
