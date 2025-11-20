<?php

namespace WezomCms\Users\Services;

use App\Exceptions\UserCarException;
use WezomCms\Firebase\UseCase\CallPushEvent;
use WezomCms\Requests\Helpers\RequestEvents;
use WezomCms\Users\Converts\CarNumberConvert;
use WezomCms\Users\Models\Car;
use WezomCms\Users\Models\User;
use WezomCms\Users\Repositories\CarRepository;
use WezomCms\Users\Types\UserCarNikoStatus;
use WezomCms\Users\Types\UserCarStatus;

final class UserCarService
{
    private $permissibleCountCar;

    public function __construct()
    {
        $settings = settings('car.page-settings', []);
        $this->permissibleCountCar = array_get($settings, 'count-cars-for-user') ?? Car::DEFAULT_PERMISSIBLE_COUNT;
    }

    /**
     * @param array $data
     * @param User $user
     * @throws \Exception
     */
    public function addCar(array $data, User $user, $status = UserCarStatus::ACTIVE, $verifyRequest = true): Car
    {
        if($verifyRequest && $user->countCar() >= $this->permissibleCountCar){
            throw  new UserCarException(__('cms-users::admin.exception.Garage limit exceeded', ['count' => $this->permissibleCountCar]));
        }

        $model = new Car();
        $model->user_id = $user->id;
        $model->is_family_car = isset($data['isFamilyCar']) ? filter_var($data['isFamilyCar'], FILTER_VALIDATE_BOOLEAN) : false;
        $model->vin_code = $data['vinCode'] ?? null;
        $model->number = $data['number'] ?? null;
        $model->year = $data['year'] ?? null;
        $model->dealership_id = isset($data['dealerCenterId']) ? (int)$data['dealerCenterId'] : null;
        $model->brand_id = isset($data['brandId']) ? (int)$data['brandId'] : null;
        $model->model_id = isset($data['modelId']) ? (int)$data['modelId'] : null;
        $model->transmission_id = isset($data['transmissionId']) ? (int)$data['transmissionId'] : null;
        $model->engine_type_id = isset($data['engineId']) ? $data['engineId'] : null;
        $model->engine_volume = $data['engineVolume'] ?? null;
        $model->status = $status;
        if(isset($data['number'])){
            $model->number_for_1c = CarNumberConvert::for1c($data['number']);
        }

        if(!$model->save()){
            throw new \Exception('Not create car for user');
        }

        if($verifyRequest){
            RequestEvents::verifyCar($model);
            CallPushEvent::verifyCar($user);
        }

        return $model;
    }

    /**
     * @param array $data
     * @param User $user
     * @return User
     * @throws \Exception
     */
    public function addCars(array $data, User $user): void
    {
        $repository = \App::make(CarRepository::class);
        foreach ($data as $dataCar){
            if(!$repository->existCar($user->id, $dataCar['number'])){
                $this->addCar($dataCar, $user);
            }
        }
    }

    public function deletedCar(Car $car)
    {
        $car->status = UserCarStatus::DELETED;
        $car->save();
    }

    /**
     *
     * @param Car $car
     * @param array $data
     * @return Car
     * @throws \Exception
     */
    public function setNikoStatus(Car $car, $status)
    {
        \DB::beginTransaction();

        try {

            $car->niko_status = $status;
            if($status == UserCarNikoStatus::VERIFY){
                $car->is_verify = true;
            } else {
                $car->is_verify = false;
            }

            $car->save();

            \DB::commit();

            return $car;

        } catch(\Exception $exception) {
            \DB::rollBack();
            \Log::error($exception->getMessage());

            throw new \Exception($exception->getMessage());
        }
    }

}
