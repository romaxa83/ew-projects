<?php

namespace App\GraphQL\Mutations\Catalog\Car;

use App\GraphQL\BaseGraphQL;
use App\Models\Admin\Admin;
use App\Models\Catalogs\Car\Brand;
use App\Repositories\Catalog\Car\BrandRepository;
use App\Services\Catalog\Car\BrandService;
use App\Services\Telegram\TelegramDev;
use GraphQL\Error\Error;

class BrandToggleMain extends BaseGraphQL
{
    public function __construct(
        protected BrandRepository $repository,
        protected BrandService $service
    )
    {}

    /**
     * @param  null  $_
     * @param  array<string, mixed>  $args
     *
     * @throws Error
     *
     * @return Brand
     */
    public function __invoke($_, array $args): Brand
    {
        $user = \Auth::guard(Admin::GUARD)->user();
        try {
            $model = $this->repository->findByID($args['id']);

            return $this->service->toggleMain($model);
        } catch (\Throwable $e) {
            TelegramDev::error(__FILE__, $e, $user->name, TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }
}


