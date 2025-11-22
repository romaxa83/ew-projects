<?php

namespace App\GraphQL\Mutations\Admin\Loyalty;

use App\GraphQL\BaseGraphQL;
use App\Models\Admin\Admin;
use App\Models\User\Loyalty\Loyalty;
use App\Repositories\User\LoyaltyItemRepository;
use App\Repositories\User\LoyaltyRepository;
use App\Services\Telegram\TelegramDev;
use App\Services\User\LoyaltyItemService;
use App\Services\User\LoyaltyService;
use GraphQL\Error\Error;

class LoyaltyToggleActive extends BaseGraphQL
{
    public function __construct(
        protected LoyaltyService $service,
        protected LoyaltyRepository $repository,
        protected LoyaltyItemRepository $itemRepository,
        protected LoyaltyItemService $itemService
    )
    {}

    /**
     * @param  null  $_
     * @param  array<string, mixed>  $args
     *
     * @throws Error
     *
     * @return Loyalty
     */
    public function __invoke($_, array $args): Loyalty
    {
        $user = \Auth::guard(Admin::GUARD)->user();
        try {
            /** @var $model Loyalty */
            $model = $this->service->toggleActive(
                $this->repository->findByID($args['id'])
            );

            // @todo dev-telegram
            TelegramDev::info("Программа лояльности [{$args['id']}] переключена на [{$model->active}]", $user->name);

            // переключаем все привязанные к пользователям купоны
            $items = $this->itemRepository->getAllByField('loyalty_id', $args['id']);
            foreach ($items as $item){
                $this->itemService->toggleActiveFromBase($item, $model->active);
            }

            // @todo dev-telegram
            TelegramDev::info("Переключено - [{$items->count()}] на [{$model->active}] привязанных программ лояльности", $user->name);

            return $model;
        } catch (\Throwable $e) {
            // @todo dev-telegram
            TelegramDev::error(__FILE__, $e, $user->name, TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }
}


