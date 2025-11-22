<?php

namespace App\GraphQL\Mutations\Catalog\Calc;

use App\DTO\Catalog\Calc\MileageDTO;
use App\DTO\Catalog\Calc\MileageEditDTO;
use App\GraphQL\BaseGraphQL;
use App\Models\Admin\Admin;
use App\Models\Catalogs\Calc\Mileage;
use App\Repositories\Catalog\Calc\MileageRepository;
use App\Services\Catalog\Calc\MileageService;
use App\Services\Telegram\TelegramDev;
use GraphQL\Error\Error;

class MileageEdit extends BaseGraphQL
{
    public function __construct(
        protected MileageService $service,
        protected MileageRepository $repository,
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

            $model = $this->service->edit(
                MileageEditDTO::byArgs($args),
                $this->repository->findByID($args['id'])
            );

            // @todo dev-telegram
            TelegramDev::info("Пробег ({$model->value}) отредактирован", $user->name);

            return $model;
        } catch (\Throwable $e) {
            TelegramDev::error(__FILE__, $e, $user->name, TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }
}
