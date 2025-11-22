<?php

namespace App\Services\User;

use App\Events\Firebase\FcmPush;
use App\Helpers\ConvertNumber;
use App\Models\User\Car;
use App\Models\User\OrderCar\OrderCar;
use App\Models\User\OrderCar\OrderCarStatus;
use App\Repositories\User\CarOrder\CarOrderStatusRepository;
use App\Services\Firebase\FcmAction;
use App\Services\Telegram\TelegramDev;

class CarOrderService
{
    public function __construct(
        protected CarOrderStatusRepository $carOrderStatusRepository,
    )
    {}

    public function create(array $data, Car $car): OrderCar
    {
        try {
            $model = new OrderCar();
            $model->car_id = $car->id;
            $model->payment_status = $data['paymentStatusCar'] ?? OrderCar::NONE;
            $model->order_number = $data['orderNumber'] ?? null;
            $model->files = isset($data['files']) && $data['files'] !== '' ? $data['files'] : null;
            $model->sum = ConvertNumber::fromFloatToNumber($data['sum'] ?? 0);
            $model->sum_discount = ConvertNumber::fromFloatToNumber($data['sumDiscount'] ?? 0);

            $model->save();

            $statuses = $this->carOrderStatusRepository->getAll();
            foreach ($statuses as $k => $status){
                $s = new OrderCarStatus();
                $s->order_car_id = $model->id;
                $s->status_id = $status->id;
                if(0 == $k){
                    $s->status = OrderCarStatus::STATUS_CURRENT;
                } else {
                    $s->status = OrderCarStatus::STATUS_WAIT;
                }
                $s->save();
            }

            TelegramDev::info("🔄 ADD ORDER CAR: [{$model->id}], add [{$statuses->count()}] statuses",);

            return $model;
        } catch (\Throwable $e) {
            \Log::error($e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }

    public function editStatus(OrderCarStatus $model, array $data): OrderCarStatus
    {
        try {
            $model->status = $data['state'] ?? $model->status;
            $model->date_at = $data['dateAt'] ?? $model->date_at;
            $model->save();

            // если статус 9 (Сделать обычным авто) и состояние done
            // переводив авто в состояние обычного авто
            if(isset($data['state']) && $data['state'] === OrderCarStatus::STATUS_DONE){
                if($model->statusName->isExitFromOrder()){
                    $car = $model->carOrder->car()->first();
                    $car->inner_status = Car::VERIFY;
                    $car->is_verify = true;
                    $car->is_order = false;

                    $car->save();

                    $model->load(['carOrder.car.user']);

                    event(new FcmPush(
                        $model->carOrder->car->user,
                        FcmAction::create(FcmAction::CAN_ADD_CAR_TO_GARAGE, [], $model->carOrder->car->user),
                        $model->carOrder->car
                    ));
                }
            }

            return $model;
        } catch (\Throwable $e) {
            \Log::error($e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }
}

