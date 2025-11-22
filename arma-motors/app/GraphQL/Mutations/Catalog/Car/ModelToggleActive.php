<?php

namespace App\GraphQL\Mutations\Catalog\Car;

use App\GraphQL\BaseGraphQL;
use App\Models\Admin\Admin;
use App\Models\Catalogs\Car\Model;
use App\Repositories\Catalog\Car\ModelRepository;
use App\Services\Catalog\Car\ModelService;
use App\Services\Telegram\TelegramDev;
use GraphQL\Error\Error;

class ModelToggleActive extends BaseGraphQL
{
    public function __construct(
        protected ModelRepository $repository,
        protected ModelService $service
    )
    {}

    /**
     * @param  null  $_
     * @param  array<string, mixed>  $args
     *
     * @throws Error
     *
     * @return Model
     */
    public function __invoke($_, array $args): Model
    {
        $user = \Auth::guard(Admin::GUARD)->user();
        try {
            $model = $this->repository->findByID($args['id']);

            return $this->service->toggleActive($model);
        } catch (\Throwable $e) {
            TelegramDev::error(__FILE__, $e, $user->name, TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }
}
