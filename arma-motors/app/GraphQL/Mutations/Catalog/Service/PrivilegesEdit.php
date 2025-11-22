<?php

namespace App\GraphQL\Mutations\Catalog\Service;

use App\DTO\Catalog\Service\PrivilegesEditDTO;
use App\GraphQL\BaseGraphQL;
use App\Models\Admin\Admin;
use App\Models\Catalogs\Service\Privileges;
use App\Repositories\Catalog\Service\PrivilegesRepository;
use App\Services\Catalog\Service\PrivilegesService;
use App\Services\Telegram\TelegramDev;
use GraphQL\Error\Error;

class PrivilegesEdit extends BaseGraphQL
{
    public function __construct(
        protected PrivilegesService $service,
        protected PrivilegesRepository $repository
    )
    {}

    /**
     * @param  null  $_
     * @param  array<string, mixed>  $args
     *
     * @throws Error
     *
     * @return Privileges
     */
    public function __invoke($_, array $args): Privileges
    {
        $user = \Auth::guard(Admin::GUARD)->user();
        try {
            $model = $this->repository->findByID($args['id']);
            $dto = PrivilegesEditDTO::byArgs($args);
            $model = $this->service->edit($dto, $model);

            // @todo dev-telegram
            TelegramDev::info("Льгота ({$model->current->name}) отредактирована", $user->name);

            return $model;
        } catch (\Throwable $e) {
            TelegramDev::error(__FILE__, $e, $user->name,TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }
}
