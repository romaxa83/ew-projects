<?php

namespace App\GraphQL\Mutations\Catalog\Car;

use App\GraphQL\BaseGraphQL;
use App\Models\Admin\Admin;
use App\Models\Catalogs\Car\EngineVolume;
use App\Repositories\Catalog\Car\EngineVolumeRepository;
use App\Services\Catalog\Car\EngineVolumeService;
use App\Services\Telegram\TelegramDev;
use GraphQL\Error\Error;

class EngineVolumeToggleActive extends BaseGraphQL
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
            /** @var $model EngineVolume */
            $model = $this->service->toggleActive(
                $this->repository->findByID($args['id'])
            );

            return $model;
        } catch (\Throwable $e) {
            TelegramDev::error(__FILE__, $e, $user->name, TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }
}

