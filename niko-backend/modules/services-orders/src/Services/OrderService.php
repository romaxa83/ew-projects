<?php

namespace WezomCms\ServicesOrders\Services;

use App\Exceptions\RejectOrderStatusException;
use Carbon\Carbon;
use WezomCms\Core\UseCase\DateFormatter;
use WezomCms\Firebase\UseCase\CallPushEvent;
use WezomCms\ServicesOrders\Helpers\Price;
use WezomCms\ServicesOrders\Http\Requests\Api\OrderChangeStatusFrom1CRequest;
use WezomCms\ServicesOrders\Models\ServicesOrder;
use WezomCms\ServicesOrders\Types\OrderStatus;
use WezomCms\TelegramBot\Telegram;
use WezomCms\Users\Models\User;
use WezomCms\Users\Services\UserCarService;
use WezomCms\Users\Types\UserCarStatus;

class OrderService
{
    /**
     * @param array $data
     * @param User $user
     * @param $serviceId
     * @return ServicesOrder
     * @throws \Throwable
     */
    public function create(array $data, User $user, $serviceId): ServicesOrder
    {
        \DB::beginTransaction();
        try {
            $order = new ServicesOrder();
            $order->user_id = $user->id;
            $order->service_group_id = $serviceId;
            $order->dealership_id = $data['dealerCenterId'];
            $order->city_id = $data['cityId'] ?? null;
            $order->comment = $data['comment'] ?? null;
            $order->recall = $data['recall'] ?? false;
            $order->on_date = isset($data['timestamp']) ? DateFormatter::convertTimestampForBack($data['timestamp']) : null;
            $order->status = OrderStatus::CREATED;
            $order->mileage = $data['mileage'] ?? 0;
            $order->service_id = $data['serviceType'] ?? null;

            if(isset($data['usersVehicleId'])){
                $order->car_id = $data['usersVehicleId'];
                $order->is_users_vehicle = true;
            } else {
                if(isset($data['anotherVehicle'])){
                    $userCarService = \App::make(UserCarService::class);
                    $car = $userCarService->addCar($data['anotherVehicle'], $user, UserCarStatus::FROM_ORDER, false);
                    $order->car_id = $car->id;
                } else {
                    throw new \Exception('не могу создать заявку, нет данных по авто');
                }
            }

            $order->save();

            \DB::commit();

            return $order;
        } catch(\Exception $exception) {
            \DB::rollBack();
            \Log::error($exception->getMessage());

            throw new \Exception($exception->getMessage());
        }
    }

    /**
     * @param ServicesOrder $order
     * @param $status
     * @return ServicesOrder
     * @throws \Exception
     */
    public function setStatus(ServicesOrder $order , $status): ServicesOrder
    {
        if(!OrderStatus::checkStatus($status)){
            throw new \Exception(__('cms-services-orders::site.exceptions.undefined status', ['status' => $status]));
        }

        $order->status = $status;
        $order->save();

        return $order;
    }

    /**
     * @param ServicesOrder $order
     * @param OrderChangeStatusFrom1CRequest $request
     * @return ServicesOrder
     * @throws \Exception
     */
    public function setStatusFrom1C(
        ServicesOrder $order,
        OrderChangeStatusFrom1CRequest $request
    )
    {
        if(OrderStatus::isRejected($order->status)){
            throw new RejectOrderStatusException(__('cms-services-orders::site.exceptions.order is have reject status'));
        }

        if(!OrderStatus::checkStatus($request['ApplicationStatusID'])){
            throw new \Exception(__('cms-services-orders::site.exceptions.undefined status', ['status' => $request['ApplicationStatusID']]));
        }

        $order->status = (int)$request['ApplicationStatusID'];
        $order->final_order_cost = isset($request['FinalOrderCost']) ? Price::toDB($request['FinalOrderCost']) : 0;
        $order->spare_parts_discount = isset($request['SparePartsDiscount']) ? Price::toDB($request['SparePartsDiscount']) : 0;
        $order->service_discount = isset($request['ServicesDiscount']) ? Price::toDB($request['ServicesDiscount']) : 0;
        $order->price_order_cost = isset($request['PriceOrderCost']) ? Price::toDB($request['PriceOrderCost']) : 0;

        // если заявка принята, кидаем пуш
        if(OrderStatus::isAccepted((int)$request['ApplicationStatusID'])){
            Telegram::event("Заявка на сервис принята [{$order->id}]");

            CallPushEvent::orderAccepted($order);
        }

        // если заявка закрыта, простовляем время закрытия
        if(OrderStatus::isDone((int)$request['ApplicationStatusID'])){
            $order->closed_at = Carbon::now();
            if($order->isSto()){
                CallPushEvent::rateOrder($order);
            }
        }

        // если статус заявки откланен, отправляе пуш
        if(OrderStatus::isRejected((int)$request['ApplicationStatusID'])){
            CallPushEvent::orderReject($order);
        }

        // если изменилась дата заявки, от желаемой, записываем ее и кидаем пуш
        if(isset($request['СonfirmedDataTime']) && $order->final_date === null){
            // заявка должна быть на тестдрайв или сто
            if($order->isTestDrive() || $order->isSto()){
                if($request['СonfirmedDataTime'] != $order->on_date->timestamp){
                    $order->final_date = \Carbon\Carbon::createFromTimestampUTC($request['СonfirmedDataTime']);

                    Telegram::event('Изменилась дата, на сервис, в заявке id = ' . $order->id);
                    CallPushEvent::finalDateForOrder($order);
                }
            }
        }

        $order->save();

        return $order;
    }

    /**
     * @param ServicesOrder $order
     * @param array $data
     * @return ServicesOrder
     * @throws \Exception
     */
    public function addRate(ServicesOrder $order, array $data): ServicesOrder
    {
        if(!$order->isClose()){
            throw new \Exception(__('cms-services-orders::site.exceptions.must close status'));
        }

        if(!$order->isSto()){
            throw new \Exception(__('cms-services-orders::site.exceptions.must be sto'));
        }

        $order->rating_services = $data['serviceRating'] ?? 0;
        $order->rating_order = $data['orderRating'] ?? 0;
        $order->rating_comment = $data['comment'] ?? null;
        $order->rate_date = Carbon::now();
        $order->save();

        return $order;
    }
}
