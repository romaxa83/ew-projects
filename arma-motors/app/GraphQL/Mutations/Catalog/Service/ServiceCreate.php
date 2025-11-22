<?php

namespace App\GraphQL\Mutations\Catalog\Service;

use App\DTO\Catalog\Service\ServiceDTO;
use App\GraphQL\BaseGraphQL;
use App\Models\Admin\Admin;
use App\Models\Catalogs\Service\Service;
use App\Services\Catalog\Service\ServiceService;
use App\Services\Telegram\TelegramDev;
use App\Traits\GraphqlResponse;
use GraphQL\Error\Error;

class ServiceCreate extends BaseGraphQL
{
    public function __construct(protected ServiceService $service)
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

            $dto = ServiceDTO::byArgs($args);
            $service = $this->service->create($dto);

            // @todo dev-telegram
            TelegramDev::info("Cервис ({$service->current->name}) создан", $user->name);

            return $service;
        } catch (\Throwable $e) {
            TelegramDev::error(__FILE__, $e, $user->name,TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }
}

