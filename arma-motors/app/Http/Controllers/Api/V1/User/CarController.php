<?php

namespace App\Http\Controllers\Api\V1\User;

use App\DTO\History\HistoryCarDto;
use App\Helpers\Logger\AALogger;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\V1\User\CarAddRequest;
use App\Http\Requests\Api\V1\User\CarEditRequest;
use App\Http\Requests\Api\V1\User\CarHistoryRequest;
use App\Models\User\Car;
use App\Models\User\User;
use App\Repositories\User\CarRepository;
use App\Repositories\User\UserRepository;
use App\Services\History\CarHistoryService;
use App\Services\User\CarService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Request;

class CarController extends ApiController
{
    public function __construct(
        protected CarRepository $carRepository,
        protected UserRepository $userRepository,
        protected CarService $carService,
        protected CarHistoryService $historyService
    )
    {}

    /**
     * @OA\Post (
     *     path="users/{userId}/cars/{carId}/edit",
     *     tags={"User"},
     *     security={
     *       {"Basic": {}},
     *     },
     *     summary="Edit user's car",
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/UserCarEdit")),
     *
     *     @OA\Response(response="200", description="OK", @OA\JsonContent(ref="#/components/schemas/SuccessResponse")),
     *     @OA\Response(response="401", description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response="400", description="Bad Request", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     */
    public function edit(CarEditRequest $request, $userId, $carId): JsonResponse
    {
        AALogger::info("Пришел запрос на редактирование авто пользователю - [userUUID - $userId, carUUID - $carId]", $request->all());
        try {
            $car = $this->carRepository->getByUuidAndUserUuid($userId, $carId);
            if(null === $car){
                throw new \Exception("Not found car by [userId - {$userId}], [carId - {$carId}]",  Response::HTTP_BAD_REQUEST);
            }

            $this->carService->editFromAA($request->all(), $car);

            return $this->successJsonMessage([]);
        } catch (\Exception $e){
            AALogger::error($e->getMessage());
            return $this->errorJsonMessage($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @OA\Post (
     *     path="users/{userId}/car",
     *     tags={"User"},
     *     security={
     *       {"Basic": {}},
     *     },
     *     summary="Add car to user",
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/UserCarAdd")),
     *
     *     @OA\Response(response="200", description="OK", @OA\JsonContent(ref="#/components/schemas/SuccessResponse")),
     *     @OA\Response(response="401", description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response="400", description="Bad Request", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     */
    public function add(CarAddRequest $request, $userId): JsonResponse
    {
        AALogger::info("Пришел запрос на добавление авто пользователю - [userUUID - $userId]", $request->all());
        try {
            /** @var $user User */
            $user = $this->userRepository->findOneBy('uuid', $userId);

            $this->carService->createItemFromAA($user, $request->all());

            return $this->successJsonMessage([]);
        } catch (\Exception $e){
            AALogger::error($e->getMessage());
            return $this->errorJsonMessage($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @OA\Get (
     *     path="users/car-verify/{carId}",
     *     tags={"User"},
     *     security={
     *       {"Basic": {}},
     *     },
     *     summary="Verify user's car",
     *
     *     @OA\Response(response="200", description="OK", @OA\JsonContent(ref="#/components/schemas/SuccessResponse")),
     *     @OA\Response(response="401", description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response="400", description="Bad Request", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     */
    public function verify(Request $request, $carId): JsonResponse
    {
        AALogger::info('Запрос на верификацию авто', ['id' => $carId]);
        try {
            /** @var $car Car */
            $car = $this->carRepository->getOneBy("uuid", $carId);
            if(null === $car){
                throw new \Exception("Not found car by [carId - {$carId}]",  Response::HTTP_BAD_REQUEST);
            }

            $this->carService->verify($car);

            return $this->successJsonMessage([]);
        } catch (\Exception $e){
            AALogger::error($e->getMessage());
            return $this->errorJsonMessage($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @OA\Post (
     *     path="history/cars/{carId}",
     *     tags={"History"},
     *     security={
     *       {"Basic": {}},
     *     },
     *     summary="History user's car",
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/CarHistoryRequest")),
     *
     *     @OA\Response(response="200", description="OK", @OA\JsonContent(ref="#/components/schemas/SuccessResponse")),
     *     @OA\Response(response="401", description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response="400", description="Bad Request", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     */
    public function history(CarHistoryRequest $request, $carId): JsonResponse
    {
        AALogger::info('ДАННЫЕ ПО ИСТОРИИ АВТО', ['id' => $carId]);
//        AALogger::info(array_to_json($request->all()));
        try {

            makeTransaction(fn() => $this->historyService->createOrUpdate(
                HistoryCarDto::byRequest($request->all())
            ));

            return $this->successJsonMessage([]);
        } catch (\Exception $e){
            AALogger::error($e->getMessage());
            return $this->errorJsonMessage($e->getMessage(), $e->getCode());
        }
    }
}
