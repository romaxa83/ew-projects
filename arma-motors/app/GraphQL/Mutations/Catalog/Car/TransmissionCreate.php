<?php

namespace App\GraphQL\Mutations\Catalog\Car;

use App\DTO\Catalog\Car\TransmissionDTO;
use App\GraphQL\BaseGraphQL;
use App\Models\Admin\Admin;
use App\Models\Catalogs\Car\Transmission;
use App\Services\Catalog\Car\TransmissionService;
use App\Services\Telegram\TelegramDev;
use GraphQL\Error\Error;

class TransmissionCreate extends BaseGraphQL
{
    public function __construct(
        protected TransmissionService $service,
    )
    {}

    /**
     * @param  null  $_
     * @param  array<string, mixed>  $args
     *
     * @throws Error
     *
     * @return Transmission
     */
    public function __invoke($_, array $args): Transmission
    {
        $user = \Auth::guard(Admin::GUARD)->user();
        try {
            $model = $this->service->create(
                TransmissionDTO::byArgs($args)
            );

            // @todo dev-telegram
            TelegramDev::info("Тип кпп ({$model->current->name}) СОЗДАН", $user->name);

            return $model;
        } catch (\Throwable $e) {
            TelegramDev::error(__FILE__, $e, $user->name, TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }
}
