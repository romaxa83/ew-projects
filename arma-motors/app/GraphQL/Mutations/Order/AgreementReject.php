<?php

namespace App\GraphQL\Mutations\Order;

use App\GraphQL\BaseGraphQL;
use App\Models\Agreement\Agreement;
use App\Models\User\User;
use App\Repositories\Order\AgreementRepository;
use App\Services\Order\AgreementService;
use App\Services\Order\OrderService;
use App\Services\Telegram\TelegramDev;
use GraphQL\Error\Error;

class AgreementReject extends BaseGraphQL
{
    public function __construct(
        private AgreementRepository $repo,
        private AgreementService $service,
        private OrderService $serviceOrder,
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
        $user = \Auth::guard(User::GUARD)->user();
        try {
            /** @var $model Agreement */
            $model = $this->repo->findByID($args['id']);
            $this->service->remove($model);

            return $this->successResponse(__('message.agreement.remove'));
        } catch (\Throwable $e) {
            TelegramDev::error(__FILE__, $e, $user->name,TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }
}

