<?php

namespace App\Services\User;

use App\DTO\User\CarDTO;
use App\Events\User\SaveCarFromAA;
use App\Helpers\ConvertNumber;
use App\Helpers\Logger\AALogger;
use App\Models\User\Car;
use App\Models\User\Confidant;
use App\Models\User\User;
use App\Repositories\Catalog\Car\BrandRepository;
use App\Repositories\Catalog\Car\ModelRepository;
use App\Repositories\User\CarRepository;
use App\Services\Telegram\TelegramDev;
use App\ValueObjects\CarNumber;
use App\ValueObjects\CarVin;
use DB;
use Illuminate\Database\Eloquent\Collection;

class CarService
{
    public function __construct(
        protected BrandRepository $brandRepository,
        protected ModelRepository $modelRepository,
        protected CarOrderService $carOrderService,
        protected CarRepository $carRepository,
        protected CarConfidantsService $carConfidantsService,
    )
    {}

    public function create(CarDTO $dto, User $user): Car
    {
        try {
            $model = new Car();
            $model->user_id = $user->id;
            $model->brand_id = $dto->getBrandId();
            $model->model_id = $dto->getModelId();
            $model->number = $dto->getNumber();
            $model->vin = $dto->getVin();
            $model->year = $dto->getYear();
            $model->is_personal = $dto->getIsPersonal();
            $model->inner_status = $dto->getStatus();
            $model->is_add_to_app = $dto->getIsAddToApp();
            $model->is_verify = $dto->getIsVerify();

            if(!$user->selectedCar()->first()){
                $model->selected = true;
            }
            $model->save();

            return $model;
        } catch (\Throwable $e) {
            \Log::error($e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }

    public function createFromAA(User $user, null|array $data): void
    {
        DB::beginTransaction();
        try {
            foreach ($data as $k => $item){
                $user->load('cars');
                $this->createItemFromAA($user, $item);
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error($e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }

    public function createItemFromAA(User $user, null|array $item): void
    {
        DB::beginTransaction();
        try {
            if(
                $item['brand']
                && $item['model']
                && !$user->cars->where('vin', $item['vin'])->first()
            ){
                $brand = $this->brandRepository->findOneBy('uuid', $item['brand']);
                $modelCar = $this->modelRepository->findOneBy('uuid', $item['model']);

                $model = new Car();
                $model->uuid = $item['id'];
                $model->user_id = $user->id;
                $model->brand_id = $brand->id;
                $model->model_id = $modelCar->id;
                $model->number = isset($item['number']) && $item['number'] ? new CarNumber($item['number']) : null;
                $model->vin = isset($item['vin']) && $item['vin'] ? new CarVin($item['vin']) : null;
                $model->year = $item['year'] ? deleteBackspace($item['year']) : null;
                $model->year_deal = $item['yearDeal'] ? $item['yearDeal'] : null;
                $model->is_add_to_app = false;
                $model->is_personal = $item['personal'];
                $model->is_buy = $item['buy'];
                $model->is_verify = true;
                $model->is_moderate = true;
                $model->inner_status = Car::VERIFY;
                $model->aa_status = $item['statusCar'];
                $model->in_garage = false;
                $model->owner_uuid = $item['owner'] ?? $user->uuid;
                $model->name_aa = $item['name'] ?? null;

                $model->save();

                // данные если авто в заказе
                if(isset($item['orderCar'])){
                    $model->is_order = true;
                    $model->save();

                    $this->carOrderService->create($item['orderCar'], $model);
                }

                // данные по конфиденциальным лицам
                if(isset($item['proxies']) && !empty($item['proxies']) && (current($item['proxies']) !== null)){
                    $this->carConfidantsService->createFromAA($model, $item['proxies']);
                }

                // @todo обсудить данный момент
                $model->refresh();
                if(!$user->selectedCar()->first() && false == $model->isOrder()){
                    $model->selected = true;
                }
                $model->save();

                // событие проверяет подходит ли авто для программы лояльности,
                // если да, то привязываем соответсвующую программу
                event(new SaveCarFromAA($user, $model));

                TelegramDev::info("🔄 ADD CAR FROM AA: [{$model->id}]",);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error($e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }

    public function completeFromAA(Car $model, null|array $data): void
    {
        if($data && !empty($data)){
            DB::beginTransaction();
            try {
                AALogger::info("COMPLETE CAR FROM AA", $data);

                $model->uuid = $data['id'];
                $model->number = isset($data['number']) && $data['number'] ? new CarNumber($data['number']) : $model->number;
                $model->vin = isset($data['vin']) && $data['vin'] ? new CarVin($data['vin']) : $model->vin;
                $model->year = $data['year'] ? deleteBackspace($data['year'])  : $model->year;
                $model->year_deal = $data['yearDeal'] ? $data['yearDeal'] : $model->year_deal;
                $model->is_personal = $data['personal'] ?? $model->is_personal;
                $model->is_buy = $data['buy'] ?? $model->is_buy;
                $model->is_verify = $data['verified'] ?? true;
                $model->is_moderate = true;
                $model->inner_status = Car::VERIFY;
                $model->aa_status = $data['statusCar'] ?? $model->aa_status;
                $model->owner_uuid = $data['owner'];
                $model->name_aa = $data['name'] ?? $model->name_aa;

                $model->save();

                // данные если авто в заказе
                if(isset($data['orderCar'])){
                    if(data_get($data, 'orderCar.orderNumber') != '0'){
                        $model->is_order = true;
                    }

                    $this->carOrderService->create($data['orderCar'], $model);
                }

                // данные по конфиденциальным лицам
                if(isset($data['proxies'])){
                    $this->carConfidantsService->createFromAA($model, $data['proxies']);
                }

                $model->save();

                // событие проверяет подходит ли авто для программы лояльности,
                // если да, то привязываем соответсвующую программу
                event(new SaveCarFromAA($model->user, $model));

//                TelegramDev::info("🔄 COMPLETE CAR FROM AA: [{$model->id}]",);
                DB::commit();
            } catch (\Throwable $e) {
                DB::rollBack();
                \Log::error($e->getMessage());
                throw new \Exception($e->getMessage());
            }
        }
    }

    public function editFromAA(array $data, Car $model): void
    {
        DB::beginTransaction();
        try {
            if(isset($data['number'])){
                $model->number = new CarNumber($data['number']);
            }
            if(isset($data['vin'])){
                $model->vin = new CarVin($data['vin']) ;
            }

            $model->is_verify = $data['verify'];
            if($data['verify']){
                $model->inner_status = Car::VERIFY;
            } else {
                $model->inner_status = Car::MODERATE;
            }

            if($model->carOrder){
                $model->carOrder->payment_status = $data['orderCar']['statusPayment'] ?? $model->carOrder->payment_status;
                $model->carOrder->sum = ConvertNumber::fromFloatToNumber($data['orderCar']['sum']
                    ?? ConvertNumber::fromNumberToFloat($model->carOrder->sum));
                $model->carOrder->sum_discount = ConvertNumber::fromFloatToNumber($data['orderCar']['sumDiscount']
                    ?? ConvertNumber::fromNumberToFloat($model->carOrder->sum_discount));
                $model->carOrder()->save($model->carOrder);
            }

            $model->name_aa = $data['name'] ?? $model->name_aa;
            $model->year = $data['year'] ?? $model->year;

            if(isset($data['model'])){
                $modelCar = $this->modelRepository->findOneBy('uuid', $data['model']);
                $model->model_id = $modelCar->id;
            }

            foreach ($data['proxies'] ?? [] as $item){
                /** @var $m Confidant */
                if($m = $model->confidants()->where('uuid', $item['id'])->first()){
                    $this->carConfidantsService->edit($m, $item);
                } else {
                    $this->carConfidantsService->create($model, $item);
                }
            }

            $model->save();

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error($e->getMessage());
            throw new \Exception($e->getMessage(), $e->getCode());
        }
    }



    public function verify(Car $model): void
    {
        DB::beginTransaction();
        try {

            $model->is_verify = true;
            $model->inner_status = Car::VERIFY;

            $model->save();

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error($e->getMessage());
            throw new \Exception($e->getMessage(), $e->getCode());
        }
    }

    public function setMain(Collection $cars, $carId): Car
    {
        DB::beginTransaction();
        try {
            foreach($cars->where('selected', true) as $item){
                $item->selected = false;
                $item->save();
            }

            $car = $cars->where('id', $carId)->first();
            if(null == $car){
                throw new \InvalidArgumentException(__('error.not found user car'));
            }
            if(!$car->isVerify()){
                throw new \DomainException(__('error.car must be verify'));
            }

            $car->selected = true;
            $car->save();

            DB::commit();

            return $car;
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error($e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }

    public function toggleModerateStatus(Car $car, bool $save = true): Car
    {
        try {
            $car->inner_status = Car::MODERATE;
            $car->is_moderate = true;
            // @todo-aa временное решение, убрать когда аа реализует свою часть
            $car->is_verify = true;

            if($save) {
                $car->save();
            }

            return $car;
        } catch (\Throwable $e) {
            \Log::error($e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }

    public function setHasInsurance(Car $car,bool $hasInsurance): Car
    {
        try {
            $car->has_insurance = $hasInsurance;
            $car->save();

            return $car;
        } catch (\Throwable $e) {
            \Log::error($e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }

    public function addCarsToGarage(array $ids): void
    {
        try {
            foreach($ids as $id){
                /** @var $model Car */
                $model = $this->carRepository->getByID($id);
                if(null !== $model){
                    $model->in_garage = true;
                    $model->save();
                }
            }
        } catch (\Throwable $e) {
            \Log::error($e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }

    public function deleteCarsToGarage(array $ids): void
    {
        try {
            foreach($ids as $id){
                /** @var $model Car */
                $model = $this->carRepository->getByID($id);
                if(null !== $model){
                    $model->in_garage = false;
                    $model->save();
                }
            }
        } catch (\Throwable $e) {
            \Log::error($e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }

    public function changeStatusStatus(Car $car, int $status): Car
    {
        try {
            Car::assertStatus($status);

            if(Car::statusModerate($status)){
                $this->toggleModerateStatus($car, false);
            }

            $car->inner_status = $status;
            $car->save();

            return $car;
        } catch (\Throwable $e) {
            \Log::error($e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }

    public function deleteCarFromUser(User $user, $carId, string $reason, $comment = null): bool
    {
        try {
            /** @var $car Car */
            $car = $user->cars()->where('id', $carId)->first();

            if(null == $car){
                throw new \InvalidArgumentException(__('error.not found user car'));
            }

            $car->delete_reason = $reason;
            $car->delete_comment = $comment;

            // если авто выбранное , обнуляем выбираем другое
            if($car->selected){
                $car->selected = false;

                // если есть другое авто , делаем его выбранным
                // @todo уточнить если есть другое авто и мы его переключаем в статус "выбраного", должно ли оно быть верефицировано
                $anotherCar = $user->cars()->where('id', '!=', $car->id)->first();
                if(null !== $anotherCar){
                    $anotherCar->selected = true;
                    $anotherCar->save();
                }
            }

            $car->save();

            return $this->delete($car);
        } catch (\Throwable $e) {
            \Log::error($e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }

    public function delete(Car $car, bool $force = false): bool
    {
        try {
            if($force){
                return $this->forceDelete($car);
            }

            return $car->delete();
        } catch (\Throwable $e) {
            \Log::error($e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }

    public function forceDelete(Car $car): bool
    {
        try {
            return $car->forceDelete();
        } catch (\Throwable $e) {
            \Log::error($e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }

    public function restore(Car $model): Car
    {
        try {
            if(!$model->trashed()){
                throw new \Exception(__('error.model not trashed'));
            }

            $model->restore();

            return $model;
        } catch (\Throwable $e) {
            \Log::error($e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }
}

