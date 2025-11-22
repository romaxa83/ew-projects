<?php

namespace App\GraphQL\Mutations\Catalog\Car;

use App\DTO\Catalog\Car\FuelEditDTO;
use App\GraphQL\BaseGraphQL;
use App\Models\Admin\Admin;
use App\Models\Catalogs\Car\Fuel;
use App\Repositories\Catalog\Car\FuelRepository;
use App\Services\Catalog\Car\FuelService;
use App\Services\Telegram\TelegramDev;
use GraphQL\Error\Error;

class FuelEdit extends BaseGraphQL
{
    public function __construct(
        protected FuelService $service,
        protected FuelRepository $repository
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
            $model = $this->service->edit(
                FuelEditDTO::byArgs($args),
                $this->repository->findByID($args['id'])
            );

            // @todo dev-telegram
            TelegramDev::info("Тип топлива ({$model->current->name}) отредактирована", $user->name);

            return $model;
        } catch (\Throwable $e) {
            TelegramDev::error(__FILE__, $e, $user->name, TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }
}
