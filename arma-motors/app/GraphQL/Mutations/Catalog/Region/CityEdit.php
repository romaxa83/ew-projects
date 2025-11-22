<?php

namespace App\GraphQL\Mutations\Catalog\Region;

use App\DTO\Catalog\Region\CityDTO;
use App\GraphQL\BaseGraphQL;
use App\Models\Admin\Admin;
use App\Models\Catalogs\Region\City;
use App\Repositories\Catalog\Region\CityRepository;
use App\Services\Catalog\Region\CityService;
use App\Services\Telegram\TelegramDev;
use GraphQL\Error\Error;

class CityEdit extends BaseGraphQL
{
    public function __construct(
        protected CityRepository $cityRepository,
        protected CityService $cityService
    )
    {}

    /**
     * @param  null  $_
     * @param  array<string, mixed>  $args
     *
     * @throws Error
     *
     * @return City
     */
    public function __invoke($_, array $args): City
    {
        $user = \Auth::guard(Admin::GUARD)->user();
        try {
            $city = $this->cityRepository->findByID($args['id']);
            $dto = CityDTO::byArgs($args);

            return $this->cityService->edit($dto, $city);
        } catch (\Throwable $e) {
            TelegramDev::error(__FILE__, $e, $user->name,TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }
}
