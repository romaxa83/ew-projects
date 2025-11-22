<?php

namespace App\GraphQL\Mutations\Support;

use App\GraphQL\BaseGraphQL;
use App\Models\Admin\Admin;
use App\Models\Support\Category;
use App\Repositories\Support\CategoryRepository;
use App\Services\Support\CategoryService;
use App\Services\Telegram\TelegramDev;
use GraphQL\Error\Error;

class SupportCategoryToggleActive extends BaseGraphQL
{
    public function __construct(
        protected CategoryService $service,
        protected CategoryRepository $repository,
    )
    {}

    /**
     * @param  null  $_
     * @param  array<string, mixed>  $args
     *
     * @throws Error
     *
     * @return Category
     */
    public function __invoke($_, array $args): Category
    {
        /** @var $user Admin */
        $user = \Auth::guard(Admin::GUARD)->user();
        try {
            /** @var $model Category */
            $model = $this->service->toggleActive(
                $this->repository->findByID($args['id'])
            );
            // @todo dev-telegram
            TelegramDev::info("Админ переключил статус категории для тех. поддержки ({$model->current->name})", $user->name);

            return $model;
        } catch (\Throwable $e) {
            TelegramDev::error(__FILE__, $e, $user->name,TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }
}
