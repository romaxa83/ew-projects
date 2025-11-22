<?php

namespace App\GraphQL\Mutations\Catalog\Service;

use App\GraphQL\BaseGraphQL;
use App\Models\Admin\Admin;
use App\Models\Catalogs\Service\DriverAge;
use App\Models\Catalogs\Service\InsuranceFranchise;
use App\Repositories\Catalog\Service\DriverAgeRepository;
use App\Repositories\Catalog\Service\InsuranceFranchiseRepository;
use App\Services\Catalog\Service\DriverAgeService;
use App\Services\Catalog\Service\InsuranceFranchiseService;
use App\Services\Telegram\TelegramDev;
use GraphQL\Error\Error;

class InsuranceFranchiseToggleActive extends BaseGraphQL
{
    public function __construct(
        protected InsuranceFranchiseService $service,
        protected InsuranceFranchiseRepository $repository
    )
    {}

    /**
     * @param  null  $_
     * @param  array<string, mixed>  $args
     *
     * @throws Error
     *
     * @return InsuranceFranchise
     */
    public function __invoke($_, array $args): InsuranceFranchise
    {
        $user = \Auth::guard(Admin::GUARD)->user();
        try {
            /** @var $model InsuranceFranchise */
            $model = $this->repository->findByID($args['id']);
            $model = $this->service->toggleActive($model);

            return $model;
        } catch (\Throwable $e) {
            TelegramDev::error(__FILE__, $e, $user->name,TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }
}

