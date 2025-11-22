<?php

namespace App\GraphQL\Mutations\Catalog\Calc;

use App\DTO\Catalog\Calc\WorkDTO;
use App\GraphQL\BaseGraphQL;
use App\Models\Admin\Admin;
use App\Models\Catalogs\Calc\Work;
use App\Services\Catalog\Calc\WorkService;
use App\Services\Telegram\TelegramDev;
use GraphQL\Error\Error;

class WorkCreate extends BaseGraphQL
{
    public function __construct(
        protected WorkService $service,
    )
    {}

    /**
     * @param  null  $_
     * @param  array<string, mixed>  $args
     *
     * @throws Error
     *
     * @return Work
     */
    public function __invoke($_, array $args): Work
    {
        $user = \Auth::guard(Admin::GUARD)->user();
        try {
            $model = $this->service->create(
                WorkDTO::byArgs($args)
            );

            // @todo dev-telegram
            TelegramDev::info("Работа, для калькулятора, ({$model->current->name}) СОЗДАНА", $user->name);

            return $model;
        } catch (\Throwable $e) {
            TelegramDev::error(__FILE__, $e, $user->name, TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }
}
