<?php

namespace App\GraphQL\Mutations\Promotion;

use App\GraphQL\BaseGraphQL;
use App\Models\Admin\Admin;
use App\Models\User\User;
use App\Repositories\Promotion\PromotionRepository;
use App\Services\Promotion\PromotionService;
use App\Services\Telegram\TelegramDev;
use GraphQL\Error\Error;

class PromotionDelete extends BaseGraphQL
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
     * @return array
     */
    public function __invoke($_, array $args): array
    {
        /** @var $user User */
        $user = \Auth::guard(Admin::GUARD)->user();
        try {
            $this->service->delete($this->repository->findByID($args['id']));

            // @todo dev-telegram
            TelegramDev::info("Удалена акция - {$args['id']}", $user->name ?? null);

            return $this->successResponse(__('message.promotion.promotion delete'));
        } catch (\Throwable $e) {
            TelegramDev::error(__FILE__, $e, $user->name,TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }
}
