<?php

namespace App\GraphQL\Mutations\Catalog\Car;

use App\DTO\Catalog\Car\EngineVolumeDTO;
use App\DTO\Catalog\Car\EngineVolumeEditDTO;
use App\GraphQL\BaseGraphQL;
use App\Models\Admin\Admin;
use App\Models\Catalogs\Car\EngineVolume;
use App\Repositories\Catalog\Car\EngineVolumeRepository;
use App\Services\Catalog\Car\EngineVolumeService;
use App\Services\Telegram\TelegramDev;
use GraphQL\Error\Error;

class EngineVolumeEdit extends BaseGraphQL
{
    public function __construct(
        protected EngineVolumeService $service,
        protected EngineVolumeRepository $repository,
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
            $model = $this->service->edit(
                EngineVolumeEditDTO::byArgs($args),
                $this->repository->findByID($args['id'])
            );

            // @todo dev-telegram
            TelegramDev::info("Обьем двигателя ({$model->volume->getValue()}) отредактирован", $user->name);

            return $model;
        } catch (\Throwable $e) {
            TelegramDev::error(__FILE__, $e, $user->name, TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }
}
