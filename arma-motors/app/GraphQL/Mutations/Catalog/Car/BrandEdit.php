<?php

namespace App\GraphQL\Mutations\Catalog\Car;

use App\DTO\Catalog\Car\BrandEditDTO;
use App\GraphQL\BaseGraphQL;
use App\Models\Admin\Admin;
use App\Models\Catalogs\Car\Brand;
use App\Models\Catalogs\Car\Model;
use App\Repositories\Catalog\Car\BrandRepository;
use App\Services\Catalog\Car\BrandService;
use App\Services\Telegram\TelegramDev;
use App\Traits\GraphqlResponse;
use Database\Seeders\BaseSeeder;
use GraphQL\Error\Error;

class BrandEdit extends BaseGraphQL
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
            $model = $this->service->edit(
                BrandEditDTO::byArgs($args),
                $this->repository->findByID($args['id'])
            );

            TelegramDev::info("Бренд ({$model->name}) отредактирован", $user->name);

            return $model;
        } catch (\Throwable $e) {
            TelegramDev::error(__FILE__, $e, $user->name, TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }
}
