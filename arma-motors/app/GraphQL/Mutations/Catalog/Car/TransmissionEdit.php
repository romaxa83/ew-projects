<?php

namespace App\GraphQL\Mutations\Catalog\Car;

use App\DTO\Catalog\Car\TransmissionEditDTO;
use App\GraphQL\BaseGraphQL;
use App\Models\Admin\Admin;
use App\Models\Catalogs\Car\Transmission;
use App\Repositories\Catalog\Car\TransmissionRepository;
use App\Services\Catalog\Car\TransmissionService;
use App\Services\Telegram\TelegramDev;
use GraphQL\Error\Error;

class TransmissionEdit extends BaseGraphQL
{
    public function __construct(
        protected TransmissionService $service,
        protected TransmissionRepository $repository
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
            $model = $this->repository->findByID($args['id']);
            $dto = TransmissionEditDTO::byArgs($args);
            $model = $this->service->edit($dto, $model);

            // @todo dev-telegram
            TelegramDev::info("Тип кпп ({$model->current->name}) отредактирована", $user->name);

            return $model;
        } catch (\Throwable $e) {
            // @todo dev-telegram
            TelegramDev::error(__FILE__, $e, $user->name, TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }
}
