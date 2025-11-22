<?php

namespace App\Http\Controllers\Api\V1\Recommendation;

use App\Events\Firebase\FcmPush;
use App\Helpers\Logger\AALogger;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\V1\Recommendation;
use App\Models\Recommendation\Recommendation as ModelRecommendation;
use App\Models\User\Car;
use App\Repositories\Order\RecommendationRepository;
use App\Repositories\User\CarRepository;
use App\Services\Firebase\FcmAction;
use App\Services\Order\RecommendationService;
use Illuminate\Http\JsonResponse;

class RecommendationController extends ApiController
{
    public function __construct(
        protected RecommendationRepository $repository,
        protected RecommendationService $service,
        protected CarRepository $carRepository
    )
    {}

    /**
     * @OA\Post (
     *     path="recommendations",
     *     tags={"Recommendation"},
     *     security={
     *       {"Basic": {}},
     *     },
     *     summary="Create or update recommendation",
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/RecommendationCreate")),
     *
     *     @OA\Response(response="200", description="OK", @OA\JsonContent(ref="#/components/schemas/SuccessResponse")),
     *     @OA\Response(response="401", description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response="400", description="Bad Request", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     */
    public function createOrUpdate(Recommendation\CreateRequest $request): JsonResponse
    {
        AALogger::info('Запрос на создание/обновление рекомендации', $request->all());
        try {
            /** @var $model ModelRecommendation */
            $model = $this->repository->getOneBy("uuid", $request["uuid"]);

            if($model){
                $this->service->edit($model, $request->all());
            } else {
                $data = $request->all();
                /** @var $car Car */
                $car = $this->carRepository->findOneBy("uuid", $data["auto"]);
                $data["userId"] = $car->user_id;

                $model = $this->service->create($data);

                $model->load(['user', 'car', 'order.service.current']);

                $user = $model->user;
                event(new FcmPush(
                    $user,
                    FcmAction::create(FcmAction::RECOMMEND_SERVICE, [
                        'class' => FcmAction::MODEL_RECOMMENDATION,
                        'id' => $model->id
                    ], $model),
                    $model
                ));
            }

            return $this->successJsonMessage([]);
        } catch (\Exception $e){
            AALogger::error($e->getMessage());
            return $this->errorJsonMessage($e->getMessage(), $e->getCode());
        }
    }
}

