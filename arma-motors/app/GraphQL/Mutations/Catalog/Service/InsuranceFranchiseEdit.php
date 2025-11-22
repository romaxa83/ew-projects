<?php

namespace App\GraphQL\Mutations\Catalog\Service;

use App\DTO\Catalog\Service\InsuranceFranchiseEditDTO;
use App\GraphQL\BaseGraphQL;
use App\Models\Admin\Admin;
use App\Models\Catalogs\Service\InsuranceFranchise;
use App\Repositories\Catalog\Service\InsuranceFranchiseRepository;
use App\Services\Catalog\Service\InsuranceFranchiseService;
use App\Services\Telegram\TelegramDev;
use GraphQL\Error\Error;

class InsuranceFranchiseEdit extends BaseGraphQL
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
            $model = $this->repository->findByID($args['id']);
            $dto = InsuranceFranchiseEditDTO::byArgs($args);
            $model = $this->service->edit($dto, $model);

            // @todo dev-telegram
            TelegramDev::info("Франшиза отредактирована", $user->name);

            return $model;
        } catch (\Throwable $e) {
            TelegramDev::error(__FILE__, $e, $user->name,TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }
}

