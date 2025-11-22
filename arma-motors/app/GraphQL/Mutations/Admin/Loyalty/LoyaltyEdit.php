<?php

namespace App\GraphQL\Mutations\Admin\Loyalty;

use App\DTO\User\LoyaltyEditDTO;
use App\GraphQL\BaseGraphQL;
use App\Models\Admin\Admin;
use App\Models\User\Loyalty\Loyalty;
use App\Repositories\User\LoyaltyRepository;
use App\Services\Telegram\TelegramDev;
use App\Services\User\LoyaltyService;
use GraphQL\Error\Error;

class LoyaltyEdit extends BaseGraphQL
{
    public function __construct(
        protected LoyaltyService $service,
        protected LoyaltyRepository $repository,
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

            $model = $this->service->edit(
                LoyaltyEditDTO::byArgs($args),
                $this->repository->findByID($args['id'])
            );

            // @todo dev-telegram
            TelegramDev::info("Программа лояльности отредактирована", $user->name);

            return $model;
        } catch (\Throwable $e) {
            // @todo dev-telegram
            TelegramDev::error(__FILE__, $e, $user->name, TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }
}


