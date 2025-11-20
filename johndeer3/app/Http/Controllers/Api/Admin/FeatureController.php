<?php

namespace App\Http\Controllers\Api\Admin;

use App\Events\DeactivateFeature;
use App\Http\Controllers\Api\ApiController;
use App\Http\Request\Feature\FeatureCreateRequest;
use App\Models\Report\Feature\Feature;
use App\Models\Report\Feature\FeatureValue;
use App\Repositories\Feature\FeatureRepository;
use App\Repositories\Report\ReportFeatureValueRepository;
use App\Resources\Custom\CustomFeatureValueResource;
use App\Resources\Feature\FeatureResource;
use App\Services\FeatureService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class FeatureController extends ApiController
{
    private $valuesResource;

    protected $orderBySupport = ['id', 'created_at', 'position'];
    protected $defaultOrderBy = 'position';

    public function __construct(
        protected FeatureService $service,
        protected FeatureRepository $repo,
        protected ReportFeatureValueRepository $reportFeatureValueRepository
    )
    {
        $this->valuesResource = \App::make(CustomFeatureValueResource::class);

        parent::__construct();
    }

    /**
     * @OA\Get (
     *     path="/api/admin/feature/{feature}",
     *     tags={"Features"},
     *     summary="Получение характеристики по ID (поле таблицы)",
     *     security={{"Basic": {}}},
     *
     *     @OA\Parameter(name="{feature}", in="path", required=true,
     *          description="ID характ.",
     *          @OA\Schema(type="integer", example="5")
     *     ),
     *
     *      @OA\Response(response="200", description="Характеристика (поле таблицы)",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", title="Data", type="object",
     *                  ref="#/components/schemas/FeatureResource"
     *              ),
     *              @OA\Property(property="success", title="Success", example=true),
     *         ),
     *     ),
     *     @OA\Response(response="400", description="Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     */
    public function show(Feature $feature)
    {
        try {
            return $this->successJsonMessage(
                FeatureResource::make($feature)
            );
        } catch (\Exception $error){
            return $this->errorJsonMessage($error->getMessage());
        }
    }

    /**
     * @OA\Get (
     *     path="/api/admin/feature/{feature}/toggle-active",
     *     tags={"Features"},
     *     summary="Активировать/деактивировать характеристику (поле таблицы)",
     *     security={{"Basic": {}}},
     *
     *     @OA\Parameter(name="{feature}", in="path", required=true,
     *          description="ID характ.",
     *          @OA\Schema(type="integer", example="5")
     *     ),
     *
     *      @OA\Response(response="200", description="Характеристика (поле таблицы)",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", title="Data", type="object",
     *                  ref="#/components/schemas/FeatureResource"
     *              ),
     *              @OA\Property(property="success", title="Success", example=true),
     *         ),
     *     ),
     *     @OA\Response(response="400", description="Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     */
    public function toggleActive(Feature $feature)
    {
        try {
            $feature = $this->service->toggleActive($feature);

            if(!$feature->active){
                event(new DeactivateFeature($feature));
            }

            return $this->successJsonMessage(
                FeatureResource::make($feature)
            );
        } catch (\Exception $error){
            return $this->errorJsonMessage($error->getMessage());
        }
    }

    /**
     * @OA\Get (
     *     path="/api/admin/feature/list",
     *     tags={"Features"},
     *     summary="Список характеристик для админа (поля таблицы)",
     *     security={{"Basic": {}}},
     *
     *     @OA\Parameter(name="name", in="query", required=false,
     *          description="Фильтр по названию",
     *          @OA\Schema(type="string", example="состояние поля")
     *     ),
     *     @OA\Parameter(name="eg_id", in="query", required=false,
     *          description="Фильтр по equipment-group",
     *          @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(name="type", in="query", required=false,
     *          description="Фильтр по типу",
     *          @OA\Schema(type="string", example=1, enum={1, 2})
     *     ),
     *     @OA\Parameter(name="order_by", in="query", required=false,
     *          description="Поле, по которому происходит сортировка",
     *          @OA\Schema(type="string", example="id", default="position", enum={"id", "created_at", "position"})
     *     ),
     *     @OA\Parameter(name="order_type", in="query", required=false,
     *          description="Тип сортировки",
     *          @OA\Schema(type="string", example="asc", default="desc", enum={"asc", "desc"})
     *     ),
     *
     *      @OA\Response(response="200", description="Характеристика (поля таблицы)",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", title="Data", type="array",
     *                  @OA\Items(ref="#/components/schemas/FeatureResource")
     *              ),
     *              @OA\Property(property="success", title="Success", example=true),
     *         ),
     *     ),
     *     @OA\Response(response="400", description="Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     */
    public function list(Request $request)
    {
        try {
            $features = $this->repo->getAll(
                ['translations', 'egs', 'current'],
                $request->all(),
                $this->orderDataForQuery()
            );

            return $this->successJsonMessage(
                FeatureResource::collection($features)
            );
        } catch (\Exception $error){
            return $this->errorJsonMessage($error->getMessage());
        }
    }

    /**
     * @OA\Post (
     *     path="/api/admin/feature/create",
     *     tags={"Features"},
     *     summary="Создание характеристики (поле таблицы)",
     *     security={{"Basic": {}}},
     *
     *     @OA\RequestBody(required=true,
     *           @OA\JsonContent(ref="#/components/schemas/FeatureCreateRequest")
     *     ),
     *
     *      @OA\Response(response="201", description="Характеристика (поле таблицы)",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", title="Data", type="object",
     *                  ref="#/components/schemas/FeatureResource"
     *              ),
     *              @OA\Property(property="success", title="Success", example=true),
     *         ),
     *     ),
     *     @OA\Response(response="400", description="Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     */
    public function create(FeatureCreateRequest $request)
    {
        try {
            $model = $this->service->create($request->all());

            return $this->successJsonMessage(
                FeatureResource::make($model),
                Response::HTTP_CREATED
            );
        } catch (\Exception $error){
            return $this->errorJsonMessage($error->getMessage());
        }
    }

    /**
     * @OA\Post (
     *     path="/admin/feature/update/{feature}",
     *     tags={"Features"},
     *     summary="Редактирование характеристик (поле таблицы)",
     *     security={{"Basic": {}}},
     *
     *     @OA\Parameter(name="{feature}", in="path", required=true,
     *          description="ID характ.",
     *          @OA\Schema(type="integer", example="5")
     *     ),
     *
     *     @OA\RequestBody(required=true,
     *           @OA\JsonContent(ref="#/components/schemas/FeatureCreateRequest")
     *     ),
     *
     *      @OA\Response(response="200", description="Характеристика (поле таблицы)",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", title="Data", type="object",
     *                  ref="#/components/schemas/FeatureResource"
     *              ),
     *              @OA\Property(property="success", title="Success", example=true),
     *         ),
     *     ),
     *     @OA\Response(response="400", description="Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     */
    public function update(FeatureCreateRequest $request, Feature $feature)
    {
        try {
            $feature = $this->service->update($request->all(), $feature);

            return $this->successJsonMessage(
                FeatureResource::make($feature)
            );
        } catch (\Exception $error){
            return $this->errorJsonMessage($error->getMessage());
        }
    }

    /**
     * @OA\Delete  (
     *     path="/admin/feature/{feature}",
     *     tags={"Features"},
     *     summary="Удаление характеристики (поле таблицы)",
     *     security={{"Basic": {}}},
     *
     *     @OA\Parameter(name="{feature}", in="path", required=true,
     *          description="ID характ.",
     *          @OA\Schema(type="integer", example="5")
     *     ),
     *
     *     @OA\RequestBody(required=true,
     *           @OA\JsonContent(ref="#/components/schemas/FeatureCreateRequest")
     *     ),
     *
     *     @OA\Response(response="200", description="Success", @OA\JsonContent(ref="#/components/schemas/SuccessResponse")),
     *     @OA\Response(response="400", description="Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     */
    public function delete(Feature $feature)
    {
        try {
            if($this->reportFeatureValueRepository->existBy('feature_id', $feature->id)){
                throw new \Exception(__('message.can not delete features'));
            }

            if($feature->type_feature){
                throw new \Exception(__('message.feature has type_features'));
            }

            $feature->delete();

            return $this->successJsonMessage([]);
        } catch (\Exception $error){
            return $this->errorJsonMessage($error->getMessage());
        }
    }

    /**
     * @OA\Post (
     *     path="/api/admin/feature/{featureId}/value",
     *     tags={"Features"},
     *     summary="Добавлений значений для выбора в характеристик (поле таблицы)",
     *     security={{"Basic": {}}},
     *
     *     @OA\Parameter(name="{featureId}", in="path", required=true,
     *          description="ID характ.",
     *          @OA\Schema(type="integer", example="5")
     *     ),
     *
     *     @OA\RequestBody(required=true,
     *           @OA\JsonContent(ref="#/components/schemas/ValueCreateRequest")
     *     ),
     *
     *     @OA\Response(response="201", description="Значения для характеристика (поле таблицы)",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", title="Data", type="object",
     *                 ref="#/components/schemas/CustomFeatureValueResource"
     *             ),
     *             @OA\Property(property="success", title="Success", example=true),
     *         ),
     *     ),
     *     @OA\Response(response="400", description="Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     *
     */
    public function addValue(Request $request, $featureId)
    {
        try {
            $value = $this->service->addValue($featureId, $request->all());

            return $this->successJsonMessage($this->valuesResource->fill($value), Response::HTTP_CREATED);
        } catch (\Exception $error){
            return $this->errorJsonMessage($error->getMessage());
        }
    }

    /**
     * @OA\Get (
     *     path="/api/admin/feature/{feature}/values",
     *     tags={"Features"},
     *     summary="Получить переводы по значения характеристик (поля таблицы)",
     *     security={{"Basic": {}}},
     *
     *     @OA\Parameter(name="{feature}", in="path", required=true,
     *          description="ID характ.",
     *          @OA\Schema(type="integer", example="5")
     *     ),
     *
     *     @OA\Parameter(name="forSelect", in="query", required=false,
     *          description="Получение данных для селекта (ключами будут id, в соответсвии локали)",
     *          @OA\Schema(type="boolean", example=true)
     *     ),
     *
     *     @OA\Response(response="200", description="Значения для характеристика (поле таблицы)",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", title="Data", type="array",
     *                 @OA\Items(ref="#/components/schemas/CustomFeatureValueResource")
     *             ),
     *             @OA\Property(property="success", title="Success", example=true),
     *         ),
     *     ),
     *     @OA\Response(response="400", description="Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     *
     */
    public function getValues(Request $request, Feature $feature)
    {
        try {
            $feature->load(['values', 'values.translates']);

            $dto = [];
            if($feature->values->isNotEmpty()){
                $dto = $this->valuesResource
                    ->fill($feature->values, filter_var($request['forSelect'], FILTER_VALIDATE_BOOLEAN));
            }

            return $this->successJsonMessage($dto);
        } catch (\Exception $error){
            return $this->errorJsonMessage($error->getMessage());
        }
    }

    /**
     * @OA\Delete (
     *     path="/api/admin/feature/value/{value}",
     *     tags={"Features"},
     *     summary="Удалить значения",
     *     security={{"Basic": {}}},
     *
     *     @OA\Parameter(name="{value}", in="path", required=true,
     *          description="ID значения характ.",
     *          @OA\Schema(type="integer", example="5")
     *     ),
     *
     *     @OA\Response(response="200", description="Success", @OA\JsonContent(ref="#/components/schemas/SuccessResponse")),
     *     @OA\Response(response="400", description="Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     *
     */
    public function removeValue(FeatureValue $value)
    {
        try {
            $this->service->removeValue($value);

            return $this->successJsonMessage([]);
        } catch (\Exception $error){
            return $this->errorJsonMessage($error->getMessage());
        }
    }

    /**
     * @OA\Post (
     *     path="/api/admin/update/value/{value}",
     *     tags={"Features"},
     *     summary="Добавлений значений для выбора в характеристик (поле таблицы)",
     *     security={{"Basic": {}}},
     *
     *     @OA\Parameter(name="{value}", in="path", required=true,
     *          description="ID значения характ.",
     *          @OA\Schema(type="integer", example="5")
     *     ),
     *
     *     @OA\RequestBody(required=true,
     *           @OA\JsonContent(ref="#/components/schemas/ValueCreateRequest")
     *     ),
     *
     *     @OA\Response(response="200", description="Значения для характеристика (поле таблицы)",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", title="Data", type="object",
     *                 ref="#/components/schemas/CustomFeatureValueResource"
     *             ),
     *             @OA\Property(property="success", title="Success", example=true),
     *         ),
     *     ),
     *     @OA\Response(response="400", description="Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     *
     */
    public function updateValue(Request $request, FeatureValue $value)
    {
        try {
            $value = $this->service->updateValue($value, $request->all());

            return $this->successJsonMessage($this->valuesResource->fill($value));
        } catch (\Exception $error){
            return $this->errorJsonMessage($error->getMessage());
        }
    }
}
