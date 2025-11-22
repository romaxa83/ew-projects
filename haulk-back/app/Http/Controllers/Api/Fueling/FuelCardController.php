<?php

namespace App\Http\Controllers\Api\Fueling;

use App\Enums\Fueling\FuelCardStatusEnum;
use App\Exceptions\HasRelatedEntitiesException;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Fueling\FuelCardAssignedDriverRequest;
use App\Http\Requests\Fueling\FuelCardRequest;
use App\Http\Requests\Fueling\FuelCardUpdateRequest;
use App\Http\Requests\Fueling\IndexFuelCardHistoryRequest;
use App\Http\Requests\Fueling\IndexFuelCardRequest;
use App\Http\Resources\Fueling\FuelCardHistoryPaginatedResource;
use App\Http\Resources\Fueling\FuelCardPaginatedResource;
use App\Models\Fueling\FuelCard;
use App\Models\Fueling\FuelCardHistory;
use App\Services\Fueling\FuelCardHistoryService;
use App\Services\Fueling\FuelCardService;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Log;
use Throwable;

class FuelCardController extends ApiController
{

    protected FuelCardService $service;
    protected FuelCardHistoryService $serviceFuelCard;

    public function __construct(FuelCardService $service, FuelCardHistoryService $serviceFuelCard)
    {
        parent::__construct();

        $this->service = $service;
        $this->serviceFuelCard = $serviceFuelCard;
    }

    /**
     * @param IndexFuelCardRequest $request
     * @return AnonymousResourceCollection
     *
     * @OA\Get(
     *     path="/api/fuel-cards",
     *     tags={"FuelCards"},
     *     summary="Get fuel card list",
     *     operationId="Get fuel card data",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(
     *           name="page",
     *           in="query",
     *           description="Page number",
     *           required=false,
     *           @OA\Schema(
     *               type="integer",
     *               default="5"
     *           )
     *      ),
     *      @OA\Parameter(
     *           name="per_page",
     *           in="query",
     *           description="States per page",
     *           required=false,
     *           @OA\Schema(
     *               type="integer",
     *               default="10"
     *           )
     *      ),
     *     @OA\Parameter(
     *          name="q",
     *          in="query",
     *          description="Scope for filter by card",
     *          required=false,
     *          @OA\Schema(
     *              type="string",
     *              default="name",
     *          )
     *     ),
     *     @OA\Parameter(
     *           name="provider",
     *           in="query",
     *           description="Scope for filter by provider (efs|quikq)",
     *           required=false,
     *           @OA\Schema(
     *               type="string",
     *               default="efs",
     *               enum={"efs", "quikq"},
     *           )
     *      ),
     *     @OA\Parameter(
     *           name="driver_id",
     *           in="query",
     *           description="driver_id",
     *           required=false,
     *           @OA\Schema(
     *               type="int",
     *               default="",
     *           )
     *      ),
     *     @OA\Parameter(
     *            name="status",
     *            in="query",
     *            description="Scope for filter by status (active|inactive|deleted)",
     *            required=false,
     *            @OA\Schema(
     *                type="string",
     *                default="active",
     *                enum={"active", "inactive", "deleted"},
     *            )
     *       ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/FuelCardPaginatedResource"),
     *     )
     * )
     */
    public function index(IndexFuelCardRequest $request): AnonymousResourceCollection
    {
        $this->authorize('fuel-cards');
        $filters = $request->validated();
        $status = \Arr::get($filters, 'status');
        if(!$status) {
            $filters['notStatus'] = FuelCardStatusEnum::DELETED;
        }
        $cards = FuelCard::query()
            ->when(
                $status === FuelCardStatusEnum::DELETED,
                fn(Builder $builder) => $builder->onlyTrashed()
            )
            ->filter($filters)
            ->orderBy('active', 'desc')
            ->orderBy('id', 'desc')
            ->paginate($request->per_page);

        return FuelCardPaginatedResource::collection($cards);
    }

    /**
     * @param IndexFuelCardHistoryRequest $request
     * @param FuelCard $fuelCard
     * @return AnonymousResourceCollection
     *
     * @OA\Get(
     *     path="/api/fuel-card-history/{fuelCard}",
     *     tags={"FuelCards"},
     *     summary="Get fuel card history",
     *     operationId="Get fuel card history",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="fuelCard", in="path", description="fuel card id", required=true,
     *         @OA\Schema(type="integer", default="1",)
     *     ),
     *     @OA\Parameter(
     *           name="page",
     *           in="query",
     *           description="Page number",
     *           required=false,
     *           @OA\Schema(
     *               type="integer",
     *               default="5"
     *           )
     *      ),
     *      @OA\Parameter(
     *           name="per_page",
     *           in="query",
     *           description="States per page",
     *           required=false,
     *           @OA\Schema(
     *               type="integer",
     *               default="10"
     *           )
     *      ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/FuelCardHistoryPaginatedResource"),
     *     )
     * )
     * @throws AuthorizationException
     */
    public function history(IndexFuelCardHistoryRequest $request, FuelCard $fuelCard): AnonymousResourceCollection
    {
        $this->authorize('fuel-cards');

        $cards = FuelCardHistory::query()
            ->where('fuel_card_id', $fuelCard->id)
            ->orderBy('id', 'desc')
            ->paginate($request->per_page);

        return FuelCardHistoryPaginatedResource::collection($cards);
    }

    /**
     * @param FuelCardRequest $request
     * @return FuelCardPaginatedResource|JsonResponse
     * @throws AuthorizationException
     * @throws Throwable
     * @OA\Post(path="/api/fuel-cards", tags={"FuelCards"}, summary="Create fuel card", operationId="Create fuel card", deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/ValidateOnly"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="card", in="query", description="Card number", required=true,
     *          @OA\Schema(type="string", default="55555",)
     *     ),
     *     @OA\Parameter(name="provider", in="query", description="Card provider", required=true,
     *          @OA\Schema(type="string", default="efs", enum={"efs", "quikq"})
     *     ),
     *      @OA\Parameter(name="status", in="query", description="Status", required=true,
     *           @OA\Schema(type="string", default="active", enum={"active", "inactive", "deleted"})
     *     ),
     *     @OA\Response(response=201, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/FuelCardResource")
     *     ),
     * )
     */
    public function store(FuelCardRequest $request)
    {
        $this->authorize('fuel-cards create');

        try {
            $tag = $this->service->create($request->validated());

            return FuelCardPaginatedResource::make($tag);
        } catch (Exception $exception) {
            Log::error($exception);

            return $this->makeErrorResponse($exception->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param FuelCard $fuelCard
     * @return FuelCardPaginatedResource
     * @throws AuthorizationException
     *
     * @OA\Get(
     *     path="/api/fuel-cards/{fuelCard}",
     *     tags={"FuelCards"}, summary="Get fuel card data show", operationId="Get fuel card show", deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="fuelCard", in="path", description="fuel card id", required=true,
     *         @OA\Schema(type="integer", default="1",)
     *     ),
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/FuelCardResource")
     *     ),
     * )
     */
    public function show(FuelCard $fuelCard): FuelCardPaginatedResource
    {
        $this->authorize('fuel-cards read');

        return FuelCardPaginatedResource::make($fuelCard);
    }

    /**
     * @param FuelCardUpdateRequest $request
     * @param FuelCard $fuelCard
     * @return FuelCardPaginatedResource|JsonResponse
     * @throws AuthorizationException
     * @OA\Put(
     *     path="/api/fuel-cards/{fuelCard}", tags={"FuelCards"}, summary="Update fuel card", operationId="Update fuel card", deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/ValidateOnly"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="fuelCard", in="path", description="fuel card id", required=true,
     *          @OA\Schema(type="integer", default="1",)
     *     ),
     *     @OA\Parameter(name="provider", in="query", description="Card provider", required=true,
     *           @OA\Schema(type="string", default="efs", enum={"efs", "quikq"})
     *     ),
     *     @OA\Parameter(name="status", in="query", description="Status", required=true,
     *           @OA\Schema(type="string", default="active", enum={"active", "inactive", "deleted"})
     *     ),
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/FuelCardResource")
     *     ),
     * )
     */
    public function update(FuelCardUpdateRequest $request, FuelCard $fuelCard)
    {
        $this->authorize('fuel-cards update');

        try {
            $fuelCard = $this->service->update($fuelCard, $request->validated());

            return FuelCardPaginatedResource::make($fuelCard);
        } catch (Exception $exception) {
            Log::error($exception);

            return $this->makeErrorResponse($exception->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param FuelCardAssignedDriverRequest $request
     * @param FuelCard $fuelCard
     * @return FuelCardPaginatedResource|JsonResponse
     * @throws AuthorizationException
     * @OA\Put(
     *     path="/api/fuel-cards/{fuelCard}/assigned-driver", tags={"FuelCards"}, summary="assigned fuel card", operationId="assigned fuel card", deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/ValidateOnly"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="fuelCard", in="path", description="fuel card id", required=true,
     *          @OA\Schema(type="integer", default="1",)
     *     ),
     *     @OA\Parameter(name="type", in="query", description="type", required=true,
     *           @OA\Schema(type="string", default="new", enum={"new", "replace"})
     *     ),
     *     @OA\Parameter(name="driver_id", in="query", description="driver id", required=true,
     *           @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/FuelCardResource")
     *     ),
     * )
     */
    public function assigned(FuelCardAssignedDriverRequest $request, FuelCard $fuelCard)
    {
        $this->authorize('fuel-cards update');

        try {
            $this->serviceFuelCard->assigned($fuelCard, $request->validated());

            return FuelCardPaginatedResource::make($fuelCard);
        } catch (Exception $exception) {
            Log::error($exception);

            return $this->makeErrorResponse($exception->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param FuelCardAssignedDriverRequest $request
     * @param FuelCard $fuelCard
     * @return FuelCardPaginatedResource|JsonResponse
     * @throws AuthorizationException
     * @OA\Put(
     *     path="/api/fuel-cards/{fuelCard}/unassigned-driver", tags={"FuelCards"}, summary="unassigned fuel card", operationId="unassigned fuel card", deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/ValidateOnly"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="fuelCard", in="path", description="fuel card id", required=true,
     *          @OA\Schema(type="integer", default="1",)
     *     ),
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/FuelCardResource")
     *     ),
     * )
     */
    public function unassigned(FuelCard $fuelCard)
    {
        $this->authorize('fuel-cards update');

        try {
            $this->serviceFuelCard->unassignedFuelCard($fuelCard);

            return FuelCardPaginatedResource::make($fuelCard);
        } catch (Exception $exception) {
            Log::error($exception);

            return $this->makeErrorResponse($exception->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param FuelCard $fuelCard
     * @return JsonResponse
     * @OA\Delete(
     *     path="/api/fuel-cards/{fuelCard}",
     *     tags={"FuelCards"}, summary="Delete fuel card", operationId="Delete fuel card", deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="fuelCard", in="path", description="fuel card id", required=true,
     *         @OA\Schema(type="integer", default="1",)
     *     ),
     *     @OA\Response(response=204, description="Successful operation",),
     * )
     */
    public function destroy(FuelCard $fuelCard): JsonResponse
    {
        $this->authorize('fuel-cards delete');

        try {
            $this->service->destroy($fuelCard);
        } catch (HasRelatedEntitiesException $exception) {
            return $this->makeErrorResponse(
                $this->getMessageForDestroyFailed($fuelCard),
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        } catch (Exception $exception) {
            Log::error($exception);

            return $this->makeErrorResponse($exception->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }


        return $this->makeSuccessResponse(null, Response::HTTP_NO_CONTENT);
    }

    protected function getMessageForDestroyFailed(FuelCard $fuelCard): string
    {
        return trans(
            'This tag is already used for <a href=":link">orders</a>',
            [
                'link' => str_replace('{id}', $fuelCard->id, config('frontend.orders_with_tag_filter_url'))
            ]
        );
        //return '';
    }
}
