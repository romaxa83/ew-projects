<?php

namespace App\GraphQL\Mutations\Dealership;

use App\DTO\Dealership\DealershipDTO;
use App\GraphQL\BaseGraphQL;
use App\Models\Admin\Admin;
use App\Models\Dealership\Dealership;
use App\Repositories\Dealership\DealershipRepository;
use App\Services\Dealership\DealershipService;
use App\Services\Telegram\TelegramDev;
use GraphQL\Error\Error;

class DealershipToggleActive extends BaseGraphQL
{
    public function __construct(
        protected DealershipService $service,
        protected DealershipRepository $repository,
    ){}

    /**
     * @param  null  $_
     * @param  array<string, mixed>  $args
     *
     * @throws Error
     *
     * @return Dealership
     */
    public function __invoke($_, array $args): Dealership
    {
        $user = \Auth::guard(Admin::GUARD)->user();
        try {
            /** @var $model Dealership */
            $model = $this->repository->findByID($args['id']);
            $model = $this->service->toggleActive($model);

            // @todo dev-telegram
            TelegramDev::info("Переключен статус дц", $user->name);

            return $model;

        } catch (\Throwable $e) {
            TelegramDev::error(__FILE__, $e, $user->name,TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }
}
