<?php

namespace App\GraphQL\Mutations\Catalog\Service;

use App\Events\ChangeHashEvent;
use App\GraphQL\BaseGraphQL;
use App\Models\Admin\Admin;
use App\Models\Catalogs\Service\Service;
use App\Models\Hash;
use App\Repositories\Catalog\Service\ServiceRepository;
use App\Services\Catalog\Service\ServiceService;
use App\Services\Telegram\TelegramDev;
use App\Traits\HashData;
use GraphQL\Error\Error;

class ServiceToggleActive extends BaseGraphQL
{
    use HashData;

    public function __construct(
        protected ServiceService $service,
        protected ServiceRepository $repository
    )
    {}

    /**
     * @param  null  $_
     * @param  array<string, mixed>  $args
     *
     * @throws Error
     *
     * @return Service
     */
    public function __invoke($_, array $args): Service
    {
        $user = \Auth::guard(Admin::GUARD)->user();
        try {
            /** @var $model Service */
            $model = $this->service->toggleActive(
                $this->repository->findByID($args['id'])
            );

            $this->throwEvent(Hash::ALIAS_SERVICE);

            // @todo dev-telegram
            TelegramDev::info("Cервис ({$model->current->name}) переключен статус", $user->name);

            return $model;
        } catch (\Throwable $e) {
            TelegramDev::error(__FILE__, $e, $user->name,TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }
}

