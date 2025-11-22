<?php

namespace App\GraphQL\Mutations\Catalog\Calc;

use App\DTO\Catalog\Calc\MileageDTO;
use App\GraphQL\BaseGraphQL;
use App\Models\Admin\Admin;
use App\Models\Catalogs\Calc\Mileage;
use App\Services\Catalog\Calc\MileageService;
use App\Services\Telegram\TelegramDev;
use GraphQL\Error\Error;

class MileageCreate extends BaseGraphQL
{
    public function __construct(
        protected MileageService $service,
    )
    {}

    /**
     * @param  null  $_
     * @param  array<string, mixed>  $args
     *
     * @throws Error
     *
     * @return Mileage
     */
    public function __invoke($_, array $args): Mileage
    {
        $user = \Auth::guard(Admin::GUARD)->user();
        try {

            $model = $this->service->create(
                MileageDTO::byArgs($args)
            );

            // @todo dev-telegram
            TelegramDev::info("Пробег ({$model->value}) СОЗДАН", $user->name);

            return $model;
        } catch (\Throwable $e) {
            TelegramDev::error(__FILE__, $e, $user->name, TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }
}
