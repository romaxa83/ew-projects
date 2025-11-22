<?php

namespace App\GraphQL\Mutations\Catalog\Service;

use App\DTO\Catalog\Service\ServiceEditDTO;
use App\GraphQL\BaseGraphQL;
use App\Models\Admin\Admin;
use App\Models\Catalogs\Service\Service;
use App\Repositories\Catalog\Service\ServiceRepository;
use App\Services\Catalog\Service\ServiceService;
use App\Services\Telegram\TelegramDev;
use App\Traits\GraphqlResponse;
use GraphQL\Error\Error;

class ServiceEdit extends BaseGraphQL
{
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

            $model = $this->repository->findByID($args['id']);
            $dto = ServiceEditDTO::byArgs($args);
            $service = $this->service->edit($dto, $model);

            // @todo dev-telegram
            TelegramDev::info("Cервис ({$service->current->name}) отредактирован", $user->name);

            return $service;
        } catch (\Throwable $e) {
            TelegramDev::error(__FILE__, $e, $user->name,TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }
}
