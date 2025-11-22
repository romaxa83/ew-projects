<?php

namespace App\GraphQL\Mutations\Promotion;

use App\GraphQL\BaseGraphQL;
use App\Models\Admin\Admin;
use App\Models\Promotion\Promotion;
use App\Models\User\User;
use App\Repositories\Promotion\PromotionRepository;
use App\Services\Promotion\PromotionService;
use App\Services\Telegram\TelegramDev;
use GraphQL\Error\Error;

class PromotionToggleActive extends BaseGraphQL
{
    public function __construct(
        protected PromotionService $service,
        protected PromotionRepository $repository,
    )
    {}

    /**
     * @param  null  $_
     * @param  array<string, mixed>  $args
     *
     * @throws Error
     *
     * @return Promotion
     */
    public function __invoke($_, array $args): Promotion
    {
        /** @var $user User */
        $user = \Auth::guard(Admin::GUARD)->user();
        try {
            /** @var $model Promotion */
            $model = $this->service->toggleActive(
                $this->repository->findByID($args['id'])
            );

            // @todo dev-telegram
            TelegramDev::info("Редактирование акция - {$model->current->name}", $user->name ?? null);

            return $model;
        } catch (\Throwable $e) {
            TelegramDev::error(__FILE__, $e, $user->name,TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }
}

