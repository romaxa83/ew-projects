<?php

namespace App\GraphQL\Mutations\Catalog\Calc;

use App\DTO\Catalog\Calc\SparesEditDTO;
use App\DTO\Catalog\Calc\SparesGroupEditDTO;
use App\GraphQL\BaseGraphQL;
use App\Models\Admin\Admin;
use App\Models\Catalogs\Calc\Spares;
use App\Repositories\Catalog\Calc\SparesRepository;
use App\Services\Catalog\Calc\SparesService;
use App\Services\Telegram\TelegramDev;
use GraphQL\Error\Error;

class SparesEdit extends BaseGraphQL
{
    public function __construct(
        protected SparesService $service,
        protected SparesRepository $repository,
    )
    {}

    /**
     * @param  null  $_
     * @param  array<string, mixed>  $args
     *
     * @throws Error
     *
     * @return Spares
     */
    public function __invoke($_, array $args): Spares
    {
        $user = \Auth::guard(Admin::GUARD)->user();
        try {
//dd(SparesEditDTO::byArgs($args));
            $model = $this->service->edit(
                SparesEditDTO::byArgs($args),
                $this->repository->findByID($args['id'])
            );

            // @todo dev-telegram
            TelegramDev::info("Запчасть ({$model->name}) отредактирована", $user->name);

            return $model;
        } catch (\Throwable $e) {
            TelegramDev::error(__FILE__, $e, $user->name, TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }
}


