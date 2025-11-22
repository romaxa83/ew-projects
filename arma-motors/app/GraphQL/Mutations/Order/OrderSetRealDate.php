<?php

namespace App\GraphQL\Mutations\Order;

use App\Exceptions\ErrorsCode;
use App\GraphQL\BaseGraphQL;
use App\Helpers\DateTime;
use App\Models\Admin\Admin;
use App\Models\Order\Order;
use App\Repositories\Order\OrderRepository;
use App\Services\Order\Exceptions\OrderFreeTimeException;
use App\Services\Order\OrderService;
use App\Services\Telegram\TelegramDev;
use App\Types\Order\Status;
use Carbon\Carbon;
use GraphQL\Error\Error;

class OrderSetRealDate extends BaseGraphQL
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
        /** @var $user Admin */
        $user = \Auth::guard(Admin::GUARD)->user();
        try {
            /** @var $order Order */
            $order = $this->repository->findByID($args['id'], ['service', 'additions']);

            if(!($order->service->isServiceParent() || $order->service->isBody())){
                throw new \InvalidArgumentException(__("error.order.order not support action"), ErrorsCode::BAD_REQUEST);
            }

            if(!($order->isCreated() || $order->isProcess())){
                throw new \InvalidArgumentException(__("error.order.order must be create and process status"), ErrorsCode::BAD_REQUEST);
            }

            if(null == $order->additions->dealership_id){
                throw new \InvalidArgumentException(__("error.order.order must have dealership"), ErrorsCode::BAD_REQUEST);
            }

            $from = DateTime::fromMillisecondToDate($args['realDate'] - 60000);
            $to = DateTime::fromMillisecondToDate($args['realDate'] + 60000);

            $exist = $this->repository->existOrderByTime(
                $order->additions->dealership_id,
                $order->service->id,
                $from,
                $to,
                [Status::CREATED, Status::IN_PROCESS]
            );

            if($exist){
                throw new \InvalidArgumentException(__("error.order.real time is busy"), ErrorsCode::BAD_REQUEST);
            }

            $date = DateTime::fromMillisecondToDate($args['realDate']);

            $order = $this->service->setRealTime($order, $date);

            return $order;
        } catch (\Throwable $e) {
            TelegramDev::error(__FILE__, $e, $user->name,TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }
}
