<?php

namespace WezomCms\Users\Http\Controllers\Api\V1;

use WezomCms\Core\Http\Controllers\ApiController;
use WezomCms\TelegramBot\Telegram;
use WezomCms\Users\DTO\CarDto;
use WezomCms\Users\DTO\CarListDto;
use WezomCms\Users\Http\Requests\Api\User\AddCarRequest;
use WezomCms\Users\Http\Requests\Api\Car\CarChangeStatusFrom1CRequest;
use WezomCms\Users\Models\Car;
use WezomCms\Users\Repositories\CarRepository;
use WezomCms\Users\Services\UserCarService;

class CarController extends ApiController
{
    protected CarListDto $carListDto;

    private UserCarService $userCarService;
    private CarRepository $carRepository;

    public function __construct(
        UserCarService $userCarService,
        CarRepository $carRepository
    )
    {
        parent::__construct();

        $this->carListDto = resolve(CarListDto::class);
        $this->userCarService = $userCarService;
        $this->carRepository = $carRepository;
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function list()
    {

        try {
            $user = \Auth::user();

            $user->load([
                'cars', 'cars.brand', 'cars.model', 'cars.transmission'
            ]);

            return $this->successJsonMessage(
                $this->carListDto->setCollection($user->cars)->toList()
            );

        } catch(\Exception $exception){
            return $this->errorJsonMessage($exception->getMessage());
        }
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function add(AddCarRequest $request)
    {
        try {
            $user = \Auth::user();
            // если машина с данным гос. номером уже есть у пользователя
            if($this->carRepository->existCar($user->id, $request['number'])){
                return $this->errorJsonMessage(__('cms-users::admin.exception.Exist car by number', ['number' => $request['number']]));
            }

            $car = $this->userCarService->addCar($request->all(), $user);

            $dto = \App::make(CarDto::class)->setModel($car);
            return $this->successJsonMessage(
                $dto->toArray()
            );

        } catch(\Exception $exception){

            return $this->errorJsonMessage($exception->getMessage());
        }
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function remove($id)
    {
        try {
            $user = \Auth::user();

            /** @var $car Car */
            $car = $this->carRepository->byId($id,[],'id', false);

            if($car){
                $this->userCarService->deletedCar($car);
            }

            $user->load([
                'cars', 'cars.brand', 'cars.model', 'cars.transmission'
            ]);

            return $this->successJsonMessage(
                $this->carListDto->setCollection($user->cars)->toList()
            );

        } catch(\Exception $exception){
            return $this->errorJsonMessage($exception->getMessage());
        }
    }

    /**
     * @param CarChangeStatusFrom1CRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function changeStatus(CarChangeStatusFrom1CRequest $request)
    {
        try {
            Telegram::event('От 1с пришел запрос на смену сатуса для автомобиля пользователя');
            Telegram::event(serialize($request->all()));

            $car = $this->carRepository->getBy1CData($request['AccountID'], $request['VIN'], $request['LicensePlate']);
            if(!$car){
                return $this->successJsonCustomMessage(['success' => false, 'message' => 'not found car'], 404);
            }

            $this->userCarService->setNikoStatus($car, $request['VehicleStatusID']);

            return $this->successJsonCustomMessage(['success' => true, 'message' => 'status changed'], 200);

        } catch(\Exception $exception){
            return $this->successJsonCustomMessage(['success' => false, 'message' => $exception->getMessage()], 500);
        }
    }

    public function changeStatusTestMode(CarChangeStatusFrom1CRequest $request)
    {
        try {
            Telegram::event('От 1с пришел ТЕСТОВЫЙ запрос на смену сатуса для автомобиля пользователя');

            return $this->successJsonCustomMessage(['success' => true, 'message' => 'status changed'], 200);

        } catch(\Exception $exception){
            return $this->successJsonCustomMessage(['success' => false, 'message' => $exception->getMessage()], 500);
        }
    }
}
