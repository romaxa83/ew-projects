<?php

namespace App\GraphQL\Mutations\Catalog\Car;

use App\DTO\Catalog\Car\TransportTypeEditDTO;
use App\GraphQL\BaseGraphQL;
use App\Models\Admin\Admin;
use App\Models\Catalogs\Car\TransportType;
use App\Repositories\Catalog\Car\TransportTypeRepository;
use App\Services\Catalog\Car\TransportTypeService;
use App\Services\Telegram\TelegramDev;
use GraphQL\Error\Error;

class TransportTypeEdit extends BaseGraphQL
{
    public function __construct(
        protected TransportTypeService $service,
        protected TransportTypeRepository $repository
    )
    {}

    /**
     * @param  null  $_
     * @param  array<string, mixed>  $args
     *
     * @throws Error
     *
     * @return TransportType
     */
    public function __invoke($_, array $args): TransportType
    {
        $user = \Auth::guard(Admin::GUARD)->user();
        try {
            $model = $this->repository->findByID($args['id']);
            $dto = TransportTypeEditDTO::byArgs($args);
            $model = $this->service->edit($dto, $model);

            // @todo dev-telegram
            TelegramDev::info("Тип транспорта ({$model->current->name}) отредактирована", $user->name);

            return $model;
        } catch (\Throwable $e) {
            TelegramDev::error(__FILE__, $e, $user->name, TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }
}

