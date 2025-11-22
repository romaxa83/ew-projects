<?php

namespace App\GraphQL\Mutations\Support;

use App\DTO\Support\SupportCategoryDTO;
use App\GraphQL\BaseGraphQL;
use App\Models\Admin\Admin;
use App\Models\Support\Category;
use App\Services\Support\CategoryService;
use App\Services\Telegram\TelegramDev;
use GraphQL\Error\Error;

class SupportCategoryCreate extends BaseGraphQL
{
    public function __construct(
        protected CategoryService $service,
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
            $model = $this->service->create(
                SupportCategoryDTO::byArgs($args)
            );
            // @todo dev-telegram
            TelegramDev::info("Админ создал категорию для тех. поддержки ({$model->current->name})", $user->name);

            return $model;
        } catch (\Throwable $e) {
            TelegramDev::error(__FILE__, $e, $user->name,TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }
}
