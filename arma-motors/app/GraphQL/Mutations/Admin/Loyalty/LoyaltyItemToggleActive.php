<?php

namespace App\GraphQL\Mutations\Admin\Loyalty;

use App\GraphQL\BaseGraphQL;
use App\Models\Admin\Admin;
use App\Models\User\Loyalty\LoyaltyItem;
use App\Repositories\User\LoyaltyItemRepository;
use App\Services\Telegram\TelegramDev;
use App\Services\User\LoyaltyItemService;
use GraphQL\Error\Error;

class LoyaltyItemToggleActive extends BaseGraphQL
{
    public function __construct(
        protected LoyaltyItemService $service,
        protected LoyaltyItemRepository $repository,
    )
    {}

    /**
     * @param  null  $_
     * @param  array<string, mixed>  $args
     *
     * @throws Error
     *
     * @return LoyaltyItem
     */
    public function __invoke($_, array $args): LoyaltyItem
    {
        $user = \Auth::guard(Admin::GUARD)->user();
        try {
            /** @var $model LoyaltyItem */
            $model = $this->service->toggleActive(
                $this->repository->findByID($args['id'])
            );

            return $model;
        } catch (\Throwable $e) {
            // @todo dev-telegram
            TelegramDev::error(__FILE__, $e, $user->name, TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }
}

