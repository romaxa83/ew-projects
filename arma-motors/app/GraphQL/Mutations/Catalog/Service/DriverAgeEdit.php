<?php

namespace App\GraphQL\Mutations\Catalog\Service;

use App\DTO\Catalog\Service\DriverAgeEditDTO;
use App\GraphQL\BaseGraphQL;
use App\Models\Admin\Admin;
use App\Models\Catalogs\Service\DriverAge;
use App\Repositories\Catalog\Service\DriverAgeRepository;
use App\Services\Catalog\Service\DriverAgeService;
use App\Services\Telegram\TelegramDev;
use GraphQL\Error\Error;

class DriverAgeEdit extends BaseGraphQL
{
    public function __construct(
        protected DriverAgeService $service,
        protected DriverAgeRepository $repository
    )
    {}

    /**
     * @param  null  $_
     * @param  array<string, mixed>  $args
     *
     * @throws Error
     *
     * @return DriverAge
     */
    public function __invoke($_, array $args): DriverAge
    {
        $user = \Auth::guard(Admin::GUARD)->user();
        try {

            $model = $this->repository->findByID($args['id']);
            $dto = DriverAgeEditDTO::byArgs($args);
            $model = $this->service->edit($dto, $model);

            // @todo dev-telegram
            TelegramDev::info("Возраст водителя ({$model->current->name}) отредактирована", $user->name);

            return $model;
        } catch (\Throwable $e) {
            TelegramDev::error(__FILE__, $e, $user->name,TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }
}
