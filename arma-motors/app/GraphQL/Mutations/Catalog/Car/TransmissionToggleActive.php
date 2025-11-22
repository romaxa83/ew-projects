<?php

namespace App\GraphQL\Mutations\Catalog\Car;

use App\GraphQL\BaseGraphQL;
use App\Models\Admin\Admin;
use App\Models\Catalogs\Car\Transmission;
use App\Repositories\Catalog\Car\TransmissionRepository;
use App\Services\Catalog\Car\TransmissionService;
use App\Services\Telegram\TelegramDev;
use GraphQL\Error\Error;

class TransmissionToggleActive extends BaseGraphQL
{
    public function __construct(
        protected TransmissionService $service,
        protected TransmissionRepository $repository
    )
    {}

    /**
     * @param  null  $_
     * @param  array<string, mixed>  $args
     *
     * @throws Error
     *
     * @return Transmission
     */
    public function __invoke($_, array $args): Transmission
    {
        $user = \Auth::guard(Admin::GUARD)->user();
        try {
            /** @var $model Transmission */
            $model = $this->service->toggleActive(
                $this->repository->findByID($args['id'])
            );

            return $model;
        } catch (\Throwable $e) {
            TelegramDev::error(__FILE__, $e, $user->name, TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }
}
