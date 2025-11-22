<?php

namespace App\Services\Order;

use App\DTO\Order\OrderBodyDTO;
use App\DTO\Order\OrderCreditDTO;
use App\DTO\Order\OrderEditDTO;
use App\DTO\Order\OrderInsuranceDTO;
use App\DTO\Order\OrderServiceDTO;
use App\DTO\Order\OrderSparesDTO;
use App\Events\Firebase\FcmPush;
use App\Exceptions\ErrorsCode;
use App\Helpers\DateTime;
use App\Helpers\Logger\OrderLogger;
use App\Helpers\Month;
use App\Models\Order\Additions;
use App\Models\Order\Order;
use App\Models\User\Car;
use App\Models\User\User;
use App\Repositories\Order\OrderRepository;
use App\Repositories\User\CarRepository;
use App\Services\BaseService;
use App\Services\Firebase\FcmAction;
use App\Types\Order\PaymentStatus;
use App\Types\Order\Status;
use Carbon\Carbon;
use DB;
use Illuminate\Database\Eloquent\Collection;

class OrderService extends BaseService
{
    public function __construct(
        protected CarRepository $carRepository,
        protected OrderRepository $repository,
        protected RecommendationService $recommendationService,
    )
    {}

    public function createInsurance(OrderInsuranceDTO $dto, User $user): Order
    {
        try {
            $model = new Order();
            $model->user_id = $user->id;
            $model->service_id = $dto->getServiceId();
            $model->communication = $dto->getCommunication();
            $model->status = Status::DRAFT;
            $model->save();

            $addition = new Additions();
            $addition->order_id = $model->id;
            $addition->franchise_id = $dto->getFranchiseId();
            $addition->brand_id = $dto->getBrandId();
            $addition->model_id = $dto->getModelId();
            $addition->driver_age_id = $dto->getDriverAgeId();
            $addition->insurance_company = $dto->getInsuranceCompany();
            $addition->count_pay = $dto->getCountPayments();
            $addition->car_cost = $dto->getCarCost();
            $addition->region_id = $dto->getRegionId();
            $addition->city_id = $dto->getCityId();
            $addition->privileges_id = $dto->getPrivilegesId();
            $addition->transport_type_id = $dto->getTransportTypeId();
            $addition->duration_id = $dto->getDurationId();
            $addition->use_as_taxi = $dto->getUseTaxi();
            $addition->save();

            return $model->refresh();

        } catch (\Throwable $e) {
            \Log::error($e->getMessage());
            throw new \Exception($e->getMessage(), $e->getCode());
        }
    }

    public function createCredit(OrderCreditDTO $dto, User $user): Order
    {
        try {
            $model = new Order();
            $model->user_id = $user->id;
            $model->service_id = $dto->getServiceId();
            $model->communication = $dto->getCommunication();
            $model->status = Status::DRAFT;
            $model->save();

            $addition = new Additions();
            $addition->order_id = $model->id;
            $addition->brand_id = $dto->getBrandId();
            $addition->model_id = $dto->getModelId();
            $addition->car_cost = $dto->getCarCost();
            $addition->duration_id = $dto->getDurationId();
            $addition->type_user = $dto->getTypeUser();
            $addition->first_installment_percent = $dto->getFirstInstallment();
            $addition->save();

            return $model->refresh();

        } catch (\Throwable $e) {
            \Log::error($e->getMessage());
            throw new \Exception($e->getMessage(), $e->getCode());
        }
    }

    public function createService(OrderServiceDTO $dto, User $user): Order
    {
        try {
            /** @var $car Car */
            $car = $this->carRepository->findOneBy('id', $dto->getCarId(), ['brand', 'model']);
            if(!$car->isVerify()){
                throw new \InvalidArgumentException(__('error.order.car must be verify'), ErrorsCode::BAD_REQUEST);
            }
            /** @var $model Order */
            $model = new Order();
            $model->user_id = $user->id;
            $model->service_id = $dto->getServiceId();
            $model->communication = $dto->getCommunication();
            $model->status = Status::DRAFT;
            $model->payment_status = PaymentStatus::NOT;
            if($dto->getRecommendationId()){
                $model->type = Order::TYPE_RECOMMEND;
            }
            if($dto->getAgreementId()){
                $model->type = Order::TYPE_AGREEMENT;
            }
            $model->save();

            $addition = new Additions();
            $addition->order_id = $model->id;
            $addition->car_id = $dto->getCarId();
            $addition->dealership_id = $dto->getDealershipId();
            $addition->recommendation_id = $dto->getRecommendationId();
            $addition->agreement_id = $dto->getAgreementId();
            $addition->comment = $dto->getComment();
            $addition->mileage = $dto->getMileage();
            $addition->brand_id = $car->brand->id;
            $addition->model_id = $car->model->id;
            if($dto->getDate() && $dto->getTime()){
                $addition->on_date = DateTime::fromMillisecondToSeconds($dto->getDate() + $dto->getTime());
            }
            $addition->for_current_filter_date = $addition->on_date;
            $addition->post_uuid = $dto->getPostUuid();

            $addition->save();

            return $model->refresh();

        } catch (\Throwable $e) {
            \Log::error($e->getMessage());
            throw new \Exception($e->getMessage(), $e->getCode());
        }
    }

    public function createFromAgreement(OrderServiceDTO $dto, User $user): Order
    {
        try {
            /** @var $car Car */
            $car = $this->carRepository->findOneBy('id', $dto->getCarId(), ['brand', 'model']);
            if(!$car->isVerify()){
                throw new \InvalidArgumentException(__('error.order.car must be verify'), ErrorsCode::BAD_REQUEST);
            }
            /** @var $model Order */
            $model = new Order();
            $model->user_id = $user->id;
            $model->service_id = $dto->getServiceId();
            $model->communication = $dto->getCommunication();
            $model->status = Status::IN_PROCESS;
            $model->payment_status = PaymentStatus::NOT;
            $model->type = Order::TYPE_AGREEMENT;
            $model->save();

            $addition = new Additions();
            $addition->order_id = $model->id;
            $addition->car_id = $dto->getCarId();
            $addition->dealership_id = $dto->getDealershipId();
            $addition->agreement_id = $dto->getAgreementId();
            $addition->brand_id = $car->brand->id;
            $addition->model_id = $car->model->id;;

            $addition->save();

            return $model->refresh();

        } catch (\Throwable $e) {
            \Log::error($e->getMessage());
            throw new \Exception($e->getMessage(), $e->getCode());
        }
    }

    public function createBody(OrderBodyDTO $dto, User $user): Order
    {
        try {
            /** @var $car Car */
            $car = $this->carRepository->findOneBy('id', $dto->getCarId(), ['brand', 'model']);
            if(!$car->isVerify()){
                throw new \InvalidArgumentException(__('error.order.car must be verify'), ErrorsCode::BAD_REQUEST);
            }

            $model = new Order();
            $model->user_id = $user->id;
            $model->service_id = $dto->getServiceId();
            $model->communication = $dto->getCommunication();
            $model->status = Status::DRAFT;
            $model->payment_status = PaymentStatus::NOT;
            if($dto->getRecommendationId()){
                $model->type = Order::TYPE_RECOMMEND;
            }
            $model->save();

            $addition = new Additions();
            $addition->order_id = $model->id;
            $addition->car_id = $dto->getCarId();
            $addition->dealership_id = $dto->getDealershipId();
            $addition->recommendation_id = $dto->getRecommendationId();
            $addition->comment = $dto->getComment();
            $addition->brand_id = $car->brand->id;
            $addition->model_id = $car->model->id;
            $addition->on_date = DateTime::fromMillisecondToSeconds($dto->getDate() + $dto->getTime());
            $addition->for_current_filter_date = $addition->on_date;
            $addition->post_uuid = $dto->getPostUuid();
            $addition->save();

            return $model->refresh();

        } catch (\Throwable $e) {
            \Log::error($e->getMessage());
            throw new \Exception($e->getMessage(), $e->getCode());
        }
    }

    public function createSpares(OrderSparesDTO $dto, User $user): Order
    {
        try {
            /** @var $car Car */
            $car = $this->carRepository->findOneBy('id', $dto->getCarId(), ['brand', 'model']);
            if(!$car->isVerify()){
                throw new \InvalidArgumentException(__('error.order.car must be verify'), ErrorsCode::BAD_REQUEST);
            }

            $model = new Order();
            $model->user_id = $user->id;
            $model->service_id = $dto->getServiceId();
            $model->communication = $dto->getCommunication();
            $model->status = Status::DRAFT;
            $model->payment_status = PaymentStatus::NOT;
            if($dto->getRecommendationId()){
                $model->type = Order::TYPE_RECOMMEND;
            }
            $model->save();

            $addition = new Additions();
            $addition->order_id = $model->id;
            $addition->car_id = $dto->getCarId();
            $addition->comment = $dto->getComment();
            $addition->brand_id = $car->brand->id;
            $addition->model_id = $car->model->id;
            $addition->recommendation_id = $dto->getRecommendationId();
            $addition->save();

            return $model->refresh();

        } catch (\Throwable $e) {
            \Log::error($e->getMessage());
            throw new \Exception($e->getMessage() ,$e->getCode());
        }
    }

    public function completeFromAA(Order $model, null|array $data): void
    {
        if($data && !empty($data)){
            DB::beginTransaction();
            try {

                $model->uuid = $data['id'];

                $model->save();
                OrderLogger::info("SET uuid [{$data['id']}] from aa for order [{$model->id}] ");
                DB::commit();
            } catch (\Throwable $e) {
                DB::rollBack();
                \Log::error($e->getMessage());
                throw new \Exception($e->getMessage());
            }
        }
    }

    public function editFromAA(Order $model, array $data): Order
    {
        if($data && !empty($data)){
            DB::beginTransaction();
            try {
                if($model->isRelateToSystem()){
                    throw new \Exception(__('error.order.order not support action'), ErrorsCode::BAD_REQUEST);
                }

                $status = Status::create($data['status']);
                // todo была просьба от заказчик, временно убрать статус оплаты
//                $paymentStatus = PaymentStatus::create($data['statusPayment']);
//
//                if($status->isDone() && $paymentStatus->isFull()){
//                    $status = Status::create(Status::CLOSE);
//                }
//                $this->changePaymentStatus($model, $paymentStatus, false);
//                if($status->isDone()){
//                    $status = Status::create(Status::CLOSE);
//                }
                $this->changeStatus($model, $status, false);

                $model->additions->responsible = $data['responsible'] ?? $model->additions->responsible;
                $model->additions->real_date = $data['realDate'] ?? $model->additions->real_date;
                $model->additions->for_current_filter_date = $model->additions->real_date;

                $model->push();

                DB::commit();

                return $model;
            } catch (\Throwable $e) {
                DB::rollBack();
                \Log::error($e->getMessage());
                throw new \Exception($e->getMessage(), $e->getCode());
            }
        }
    }

    public function setRealTime(Order $model, $date, $save = true): Order
    {
        try {
            $model->additions->real_date = $date ?? $model->additions->real_date;
            $model->additions->for_current_filter_date = $model->additions->real_date;

            if($save){
                $model->push();
            }

            return $model;
        } catch (\Throwable $e) {
            \Log::error($e->getMessage());
            throw new \Exception($e->getMessage(), $e->getCode());
        }
    }

    public function setRate(Order $model, int $rate, string $comment = null): Order
    {
        try {
            if(null === $model->additions){
                $addition = new Additions();
                $addition->order_id = $model->id;
                $addition->save();

                $model->refresh();
            }

            $model->additions->rate = $rate;
            $model->additions->rate_comment = $comment;
            $model->additions->save();

            return $model->refresh();
        } catch (\Throwable $e) {
            \Log::error($e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }

    public function edit(OrderEditDTO $dto, Order $model): Order
    {
        try {
            $model->admin_id = $dto->changeAdminId() ? $dto->getAdminId() : $model->admin_id;
            $model->status = $dto->changeStatus() ? $dto->getStatus() : $model->status;

            $model->save();

            return $model;
        } catch (\Throwable $e) {
            \Log::error($e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }

    public function changeStatus(Order $model, Status $status, bool $save = true): Order
    {
        try {
            // если заявка закрыта то нельзя менять статус
            if($model->state->isClose()){
                throw new \Exception(
                    __('error.order.not change status order close'),
                    ErrorsCode::BAD_REQUEST
                );
            }

            $model->status = $status->getValue();

            if($status->isCreated()){
                $model->load(['user']);

                if(!$model->send_push_process){
                    event(new FcmPush(
                        $model->user,
                        FcmAction::create(FcmAction::ORDER_ACCEPT, [
                            'class' => FcmAction::MODEL_ORDER,
                            'id' => $model->id
                        ], $model),
                        $model
                    ));
                    $model->update(['send_push_process' => true]);
                }
            }

//            if($status->isClose() || $status->isDone() || ($status->isClose() && $model->isRelateToSystem())){
            if($status->isDone() || ($status->isClose() && $model->isRelateToSystem())){
                $model->load(['user']);
                if(!$model->send_push_close){
                    event(new FcmPush(
                        $model->user,
                        FcmAction::create(FcmAction::ORDER_COMPLETE, [
                            'class' => FcmAction::MODEL_ORDER,
                            'id' => $model->id
                        ], $model),
                        $model
                    ));
                    $model->update(['send_push_close' => true]);
                }
            }

            if($status->isClose()){
                $model->closed_at = Carbon::now();
            }

            if($status->isReject()){
                $model->deleted_at = Carbon::now();
            }

            if($save){
                $model->save();
            }

            return $model;
        } catch (\Throwable $e) {
            \Log::error($e->getMessage());
            throw new \Exception($e->getMessage(), $e->getCode());
        }
    }

    public function changePaymentStatus(Order $model, PaymentStatus $status, bool $save = true): Order
    {
        try {
            // если заявка закрыта то нельзя менять статус
            if($model->state->isClose()){
                throw new \Exception(
                    __('error.order.not change status order close'),
                    ErrorsCode::BAD_REQUEST
                );
            }

            $model->payment_status = $status->getValue();

            if($save){
                $model->save();
            }

            return $model;
        } catch (\Throwable $e) {
            \Log::error($e->getMessage());
            throw new \Exception($e->getMessage(), $e->getCode());
        }
    }

    public function attachAdmin(Order $model, $adminId): Order
    {
        try {
            $model->admin_id = $adminId;

            if($model->isDraft()){
                $status = Status::create(Status::CREATED);
                $this->changeStatus($model, $status, false);
            }

            $model->save();

            return $model;
        } catch (\Throwable $e) {
            \Log::error($e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }

    public function delete(Order $model, bool $force = false): bool
    {
        try {
            if($force){
                return $model->forceDelete();
            }

            // заявка должна быть закрыта для удаления в архив
            if(!$model->isClose()){
                throw new \RuntimeException(__('error.order.must close status'), ErrorsCode::BAD_REQUEST);
            }

            return $model->delete();
        } catch (\Throwable $e) {
            \Log::error($e->getMessage());
            throw new \Exception($e->getMessage(), $e->getCode());
        }
    }

    public function restore(Order $model): Order
    {
        try {
            if(!$model->trashed()){
                throw new \Exception(__('error.model not trashed'));
            }
            $model->restore();

            if($model->state->isReject()){
                $status = Status::create(Status::DRAFT);
                $model = $this->changeStatus($model, $status);
            }

            return $model;
        } catch (\Throwable $e) {
            \Log::error($e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }

    public function generateBillPdfAndSave(Order $model, $data): bool
    {
        try {
            // заявка должна быть закрыта для удаления в архив
            if(!$model->isClose()){
                throw new \RuntimeException(__('error.order.must close status'), ErrorsCode::BAD_REQUEST);
            }

            return $model->delete();
        } catch (\Throwable $e) {
            \Log::error($e->getMessage());
            throw new \Exception($e->getMessage(), $e->getCode());
        }
    }

    public function getDataForCountOrdersDashboard($year, Collection $brands): array
    {
        try {
            $ids = $brands->pluck('id')->toArray();
            $namesBrand = $brands->pluck('name','id')->toArray();

            $orders = $this->repository->getByDashboardCountOrder($year, $ids);

            $data = [];
            foreach ($orders as $month => $items){
                foreach ($items as $brand => $order){
                    $data[Month::month()[$month]][$namesBrand[$brand]] = $order->count();
                }
            }

            $prettyData = [];
            // если данных по месяцу нету, заполняем их нолями
            foreach (Month::month() as $key => $month) {
                if(!isset($data[$month])){
                    $prettyData[$key]['month'] = $month;
                    foreach ($namesBrand as $k => $name){
                        $prettyData[$key]['data'][$k]['brand'] = $name;
                        $prettyData[$key]['data'][$k]['count'] = 0;
                    }
                } else {
                    $prettyData[$key]['month'] = $month;
                    $k = 1;
                    foreach ($data[$month] as $name => $count){
                        $prettyData[$key]['data'][$k]['brand'] = $name;
                        $prettyData[$key]['data'][$k]['count'] = $count;
                        $k++;
                    }

                    if(count($prettyData[$key]['data']) != count($namesBrand)){
                        foreach ($namesBrand as $name){
                            if(!in_array($name, mergeOneArray($prettyData[$key]['data']))){
                                $temp['brand'] = $name;
                                $temp['count'] = 0;
                                $prettyData[$key]['data'][] = $temp;
                            }
                        }
                    }
                }
            }

            return array_values($prettyData);
        } catch (\Throwable $e) {
            \Log::error($e->getMessage());
            throw new \Exception($e->getMessage(), $e->getCode());
        }
    }
}


