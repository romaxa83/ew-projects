<?php

namespace App\GraphQL\Queries\Promotion;

use App\GraphQL\BaseGraphQL;
use App\Models\User\User;
use App\Repositories\Promotion\PromotionRepository;
use App\Services\Telegram\TelegramDev;
use Illuminate\Database\Eloquent\Collection;

class PromotionsForUser extends BaseGraphQL
{
    public function __construct(
        protected PromotionRepository $promotionRepository,
    ){}

    /**
     * @param null                 $_
     * @param array<string, mixed> $args
     *
     * @return Collection
     * @throws \GraphQL\Error\Error
     */
    public function __invoke($_, array $args): Collection
    {
        /** @var $user User */
        $user = \Auth::guard(User::GUARD)->user();
        try {
            return $this->promotionRepository->getCommonAndIndividual($user);
        } catch (\Throwable $e) {
            TelegramDev::error(__FILE__, $e, $user->name,TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }
}
