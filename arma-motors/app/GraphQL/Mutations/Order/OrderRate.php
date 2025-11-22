<?php

namespace App\GraphQL\Mutations\Order;

use App\Exceptions\ErrorsCode;
use App\GraphQL\BaseGraphQL;
use App\Models\Order\Order;
use App\Models\User\User;
use App\Repositories\Order\OrderRepository;
use App\Services\Order\OrderService;
use App\Services\Telegram\TelegramDev;
use GraphQL\Error\Error;

class OrderRate extends BaseGraphQL
{
    public function __construct(
        protected OrderService $service,
        protected OrderRepository $repository,
    )
    {}

    /**
     * @param  null  $_
     * @param  array<string, mixed>  $args
     *
     * @throws Error
     *
     * @return Order
     */
    public function __invoke($_, array $args): Order
    {
        /** @var $user User */
        $user = \Auth::guard(User::GUARD)->user();
        try {
            if(($args['rate'] <= 3) && !isset($args['comment'])){
                throw new \InvalidArgumentException(
                    __( 'error.required comment to rate', ['rate' => $args['rate']]),
                    ErrorsCode::BAD_REQUEST
                );
            }

            $model = $this->repository->findByID($args['id']);

            $order = $this->service->setRate(
                $model,
                $args['rate'],
                $args['comment'] ?? null
            );

            TelegramDev::info("Пользователь оценил заявку на {$args['rate']} ({$model->id})", $user->name);

            return $order;
        } catch (\Throwable $e) {
            TelegramDev::error(__FILE__, $e, $user->name,TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }
}
