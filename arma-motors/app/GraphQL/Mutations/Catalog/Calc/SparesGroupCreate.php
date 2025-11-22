<?php

namespace App\GraphQL\Mutations\Catalog\Calc;

use App\DTO\Catalog\Calc\SparesGroupDTO;
use App\GraphQL\BaseGraphQL;
use App\Models\Admin\Admin;
use App\Models\Catalogs\Calc\SparesGroup;
use App\Services\Catalog\Calc\SparesGroupService;
use App\Services\Telegram\TelegramDev;
use GraphQL\Error\Error;

class SparesGroupCreate extends BaseGraphQL
{
    public function __construct(
        protected SparesGroupService $service,
    )
    {}

    /**
     * @param  null  $_
     * @param  array<string, mixed>  $args
     *
     * @throws Error
     *
     * @return SparesGroup
     */
    public function __invoke($_, array $args): SparesGroup
    {
        $user = \Auth::guard(Admin::GUARD)->user();
        try {
            $model = $this->service->create(
                SparesGroupDTO::byArgs($args)
            );

            // @todo dev-telegram
            TelegramDev::info("Группа запчастей ({$model->current->name}) СОЗДАНА", $user->name);

            return $model;
        } catch (\Throwable $e) {
            TelegramDev::error(__FILE__, $e, $user->name, TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }
}
