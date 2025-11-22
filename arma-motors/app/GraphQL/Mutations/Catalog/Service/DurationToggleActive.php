<?php

namespace App\GraphQL\Mutations\Catalog\Service;

use App\GraphQL\BaseGraphQL;
use App\Models\Admin\Admin;
use App\Models\Catalogs\Service\DriverAge;
use App\Models\Catalogs\Service\Duration;
use App\Repositories\Catalog\Service\DriverAgeRepository;
use App\Repositories\Catalog\Service\DurationRepository;
use App\Services\Catalog\Service\DriverAgeService;
use App\Services\Catalog\Service\DurationService;
use App\Services\Telegram\TelegramDev;
use GraphQL\Error\Error;

class DurationToggleActive extends BaseGraphQL
{
    public function __construct(
        protected DurationService $service,
        protected DurationRepository $repository
    )
    {}

    /**
     * @param  null  $_
     * @param  array<string, mixed>  $args
     *
     * @throws Error
     *
     * @return Duration
     */
    public function __invoke($_, array $args): Duration
    {
        $user = \Auth::guard(Admin::GUARD)->user();
        try {
            /** @var $model Duration */
            $model = $this->repository->findByID($args['id']);
            $model = $this->service->toggleActive($model);

            return $model;
        } catch (\Throwable $e) {
            TelegramDev::error(__FILE__, $e, $user->name,TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }
}
