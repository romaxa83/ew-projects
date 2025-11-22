<?php

namespace App\GraphQL\Mutations\Catalog\Car;

use App\DTO\Catalog\Car\EngineVolumeDTO;
use App\GraphQL\BaseGraphQL;
use App\Models\Admin\Admin;
use App\Models\Catalogs\Car\EngineVolume;
use App\Services\Catalog\Car\EngineVolumeService;
use App\Services\Telegram\TelegramDev;
use GraphQL\Error\Error;

class EngineVolumeCreate extends BaseGraphQL
{
    public function __construct(
        protected EngineVolumeService $service,
    )
    {}

    /**
     * @param  null  $_
     * @param  array<string, mixed>  $args
     *
     * @throws Error
     *
     * @return EngineVolume
     */
    public function __invoke($_, array $args): EngineVolume
    {
        $user = \Auth::guard(Admin::GUARD)->user();
        try {
            $model = $this->service->create(
                EngineVolumeDTO::byArgs($args)
            );

            // @todo dev-telegram
            TelegramDev::info("Обьем двигателя ({$model->volume->getValue()}) СОЗДАН", $user->name);

            return $model;
        } catch (\Throwable $e) {
            TelegramDev::error(__FILE__, $e, $user->name, TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }
}
