<?php

namespace App\GraphQL\Mutations\Catalog\Car;

use App\DTO\Catalog\Car\DriveUnitEditDTO;
use App\GraphQL\BaseGraphQL;
use App\Models\Admin\Admin;
use App\Models\Catalogs\Car\DriveUnit;
use App\Repositories\Catalog\Car\DriveUnitRepository;
use App\Services\Catalog\Car\DriveUnitService;
use App\Services\Telegram\TelegramDev;
use GraphQL\Error\Error;

class DriveUnitEdit extends BaseGraphQL
{
    public function __construct(
        protected DriveUnitService $service,
        protected DriveUnitRepository $repository,
    )
    {}

    /**
     * @param  null  $_
     * @param  array<string, mixed>  $args
     *
     * @throws Error
     *
     * @return DriveUnit
     */
    public function __invoke($_, array $args): DriveUnit
    {
        $user = \Auth::guard(Admin::GUARD)->user();
        try {
            $model = $this->service->edit(
                DriveUnitEditDTO::byArgs($args),
                $this->repository->findByID($args['id'])
            );

            // @todo dev-telegram
            TelegramDev::info("Привод ({$model->name}) отредактирован", $user->name);

            return $model;
        } catch (\Throwable $e) {
            TelegramDev::error(__FILE__, $e, $user->name, TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }
}
