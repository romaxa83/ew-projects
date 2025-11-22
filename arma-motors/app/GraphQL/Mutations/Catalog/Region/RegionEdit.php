<?php

namespace App\GraphQL\Mutations\Catalog\Region;

use App\DTO\Catalog\Region\RegionDTO;
use App\GraphQL\BaseGraphQL;
use App\Models\Admin\Admin;
use App\Models\Catalogs\Region\Region;
use App\Repositories\Catalog\Region\RegionRepository;
use App\Services\Catalog\Region\RegionService;
use App\Services\Telegram\TelegramDev;
use GraphQL\Error\Error;

class RegionEdit extends BaseGraphQL
{
    public function __construct(
        protected RegionRepository $regionRepository,
        protected RegionService $regionService
    )
    {}

    /**
     * @param  null  $_
     * @param  array<string, mixed>  $args
     *
     * @throws Error
     *
     * @return Region
     */
    public function __invoke($_, array $args): Region
    {
        $user = \Auth::guard(Admin::GUARD)->user();
        try {
            $model = $this->regionRepository->findByID($args['id']);
            $dto = RegionDTO::byArgs($args);

            return $this->regionService->edit($dto, $model);
        } catch (\Throwable $e) {
            TelegramDev::error(__FILE__, $e, $user->name,TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }
}
