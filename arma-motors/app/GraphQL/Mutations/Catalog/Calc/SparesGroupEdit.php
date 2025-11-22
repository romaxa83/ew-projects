<?php

namespace App\GraphQL\Mutations\Catalog\Calc;

use App\DTO\Catalog\Calc\SparesGroupEditDTO;
use App\GraphQL\BaseGraphQL;
use App\Models\Admin\Admin;
use App\Models\Catalogs\Calc\SparesGroup;
use App\Repositories\Catalog\Calc\SparesGroupRepository;
use App\Services\Catalog\Calc\SparesGroupService;
use App\Services\Telegram\TelegramDev;
use GraphQL\Error\Error;

class SparesGroupEdit extends BaseGraphQL
{
    public function __construct(
        protected SparesGroupService $service,
        protected SparesGroupRepository $repository,
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
            $model = $this->service->edit(
                SparesGroupEditDTO::byArgs($args),
                $this->repository->findByID($args['id'])
            );

            // @todo dev-telegram
            TelegramDev::info("Группа запчастей ({$model->current->name}) отредактирована", $user->name);

            return $model;
        } catch (\Throwable $e) {
            TelegramDev::error(__FILE__, $e, $user->name, TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }
}

