<?php

namespace App\Http\Controllers\Api\Fueling;

use App\Enums\Fueling\FuelCardProviderEnum;
use App\Enums\Fueling\FuelingHistoryStatusEnum;
use App\Exceptions\HasRelatedEntitiesException;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Fueling\FuelingFileRequest;
use App\Http\Requests\Fueling\FuelingUpdateRequest;
use App\Http\Requests\Fueling\IndexFuelingHistoryRequest;
use App\Http\Requests\Fueling\IndexFuelingNoValidRequest;
use App\Http\Requests\Fueling\IndexFuelingRequest;
use App\Http\Resources\Fueling\FuelCardPaginatedResource;
use App\Http\Resources\Fueling\FuelingHistoryPaginatedResource;
use App\Http\Resources\Fueling\FuelingPaginatedResource;
use App\Models\Fueling\Fueling;
use App\Models\Fueling\FuelingHistory;
use App\Services\Fueling\FuelingService;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Log;
use Throwable;

class FuelingController extends ApiController
{

    protected FuelingService $service;

    public function __construct(FuelingService $service)
    {
        parent::__construct();

        $this->service = $service;
    }

    /**
     * @param IndexFuelingRequest $request
     * @return AnonymousResourceCollection
     *
     * @OA\Get(
     *     path="/api/fueling",
     *     tags={"Fueling"},
     *     summary="Get valid fueling list",
     *     operationId="Get valid fueling data",
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
     *          name="card",
     *          in="query",
     *          description="Scope for filter by card",
     *          required=false,
     *          @OA\Schema(
     *              type="string",
     *              default="12345",
     *          )
     *     ),
     *      @OA\Parameter(
     *           name="driver_id",
     *           in="query",
     *           description="Scope for filter by driver_id",
     *           required=false,
     *           @OA\Schema(
     *               type="string",
     *               default="1",
     *           )
     *      ),
     *      @OA\Parameter(
     *           name="state",
     *           in="query",
     *           description="Scope for filter by state",
     *           required=false,
     *           @OA\Schema(
     *               type="string",
     *               default="NY",
     *           )
     *      ),
     *     @OA\Parameter(
     *           name="status",
     *           in="query",
     *           description="Scope for filter by status (paid|due)",
     *           required=false,
     *           @OA\Schema(
     *               type="string",
     *               default="paid",
     *               enum={"paid", "due"},
     *           )
     *      ),
     *      @OA\Parameter(
     *          name="fuel_card_status",
     *          in="query",
     *          description="Scope for filter by provider (active|inactive|deleted)",
     *          required=false,
     *          @OA\Schema(
     *              type="string",
     *              default="active",
     *              enum={"active", "inactive", "deleted"},
     *          )
     *      ),
     *      @OA\Parameter(
     *            name="transaction_date_to",
     *            in="query",
     *            description="Scope for filter by date format m/d/Y",
     *            required=false,
     *            @OA\Schema(
     *                type="string",
     *                default="10/25/2023",
     *            )
     *      ),
     *      @OA\Parameter(
     *             name="transaction_date_from",
     *             in="query",
     *             description="Scope for filter by date format m/d/Y",
     *             required=false,
     *             @OA\Schema(
     *                 type="string",
     *                 default="11/25/2023",
     *             )
     *      ),
     *      @OA\Parameter(
     *            name="source",
     *            in="query",
     *            description="Scope for filter by source (import|manually)",
     *            required=false,
     *            @OA\Schema(
     *                type="string",
     *                default="import",
     *                enum={"import", "manually"},
     *            )
     *     ),
     *     @OA\Parameter(name="transaction_date_to", in="query", description="date, format m/d/Y", required=false,
     *         @OA\Schema(type="string", default="01/20/2023",)
     *     ),
     *     @OA\Parameter(name="transaction_date_from", in="query", description="date, format m/d/Y", required=false,
     *         @OA\Schema(type="string", default="01/20/2023",)
     *     ),
     *     @OA\Parameter(name="order_by", in="query", description="Field for sort", required=false,
     *         @OA\Schema(type="string", default="status", enum ={"name"})
     *     ),
     *     @OA\Parameter(name="order_type", in="query", description="Type for sort", required=false,
     *         @OA\Schema(type="string", default="desc", enum ={"asc","desc"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/FuelingPaginatedResource"),
     *     )
     * )
     */
    public function index(IndexFuelingRequest $request): AnonymousResourceCollection
    {
        $this->authorize('fueling');

        $cards = Fueling::query()
            ->with('fuelCard', 'driver')
            ->filter($request->validated())
            ->where('valid', true)
            ->orderBy($request->order_by, $request->order_type)
            ->paginate($request->per_page);

        return FuelingPaginatedResource::collection($cards);
    }

    /**
     * @param IndexFuelingHistoryRequest $request
     * @return AnonymousResourceCollection
     * @throws AuthorizationException
     * @throws Throwable
     * @OA\Get(path="/api/fueling/history", tags={"Fueling"}, summary="All history imports", operationId="All history imports", deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/ValidateOnly"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(
     *            name="status",
     *            in="query",
     *            description="Scope for filter by provider (success|in_progress|in_queue|completed_in_errors)",
     *            required=false,
     *            @OA\Schema(
     *                type="string",
     *                default="paid",
     *                enum={"success", "in_progress", "in_queue", "completed_in_errors"},
     *            )
     *      ),
     *      @OA\Parameter(
     *             name="not_completed",
     *             in="query",
     *             description="Scope for filter by not_completed",
     *             required=false,
     *             @OA\Schema(
     *                 type="boolean",
     *                 default="true",
     *             )
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(ref="#/components/schemas/FuelingHistoryPaginatedResource"),
     *      )
     * )
     */
    public function history(IndexFuelingHistoryRequest $request): AnonymousResourceCollection
    {
        $this->authorize('fueling');

        $cards = FuelingHistory::query()
            ->filter($request->validated())
            ->orderBy('id', 'desc')
            ->paginate($request->per_page);

        return FuelingHistoryPaginatedResource::collection($cards);
    }

    /**
     * @return FuelingHistoryPaginatedResource|JsonResponse
     * @throws AuthorizationException
     * @throws Throwable
     * @OA\Get(path="/api/fueling/active-import", tags={"Fueling"}, summary="Get active history imports", operationId="Get active history imports", deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/ValidateOnly"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Response(
     *        response=200,
     *        description="Successful operation",
     *        @OA\JsonContent(ref="#/components/schemas/FuelingHistoryResource"),
     *    )
     * )
     */
    public function activeImport()
    {
        $this->authorize('fueling');
        try {
            $card = FuelingHistory::query()
                ->whereIn('status', [FuelingHistoryStatusEnum::IN_PROGRESS, FuelingHistoryStatusEnum::IN_QUEUE()])
                ->where('provider', FuelCardProviderEnum::EFS)
                ->orderBy('id', 'desc')
                ->firstOrFail();
            return FuelingHistoryPaginatedResource::make($card);
        } catch (Exception $e) {
            return $this->makeSuccessResponse(null, Response::HTTP_NO_CONTENT);
        }
    }

    /**
     * @param FuelingFileRequest $request
     * @return FuelCardPaginatedResource|JsonResponse
     * @throws AuthorizationException
     * @throws Throwable
     * @OA\Post(path="/api/fueling/import", tags={"Fueling"}, summary="Import", operationId="Import", deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/ValidateOnly"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *      @OA\Parameter(name="file", in="query", required=false,
     *           @OA\Schema(type="file")
     *     ),
     *     @OA\Parameter(name="provider", in="query", description="Card provider", required=true,
     *          @OA\Schema(type="string", default="efs", enum={"efs", "quikq"})
     *     ),
     *      @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/FuelingHistoryResource"),
     *      )
     * )
     */
    public function import(FuelingFileRequest $request): FuelingHistoryPaginatedResource
    {
        $this->authorize('fueling create');

        try {
            $args = $request->validated();
            $args['user_id'] = authUser()->getKey();
            return FuelingHistoryPaginatedResource::make($this->service->import($args));
        } catch (Exception $exception) {
            Log::error($exception);

            return $this->makeErrorResponse($exception->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param FuelingUpdateRequest $request
     * @param Fueling $fueling
     * @return FuelingPaginatedResource|JsonResponse
     * @throws AuthorizationException
     * @OA\Put(
     *     path="/api/fueling/{fueling}", tags={"Fueling"}, summary="Update fueling", operationId="Update fueling", deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/ValidateOnly"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="fueling", in="path", description="fuel card id", required=true,
     *           @OA\Schema(type="integer", default="1",)
     *     ),
     *     @OA\Parameter(name="fuel_card_id", in="query", description="Fuel Card", required=true,
     *           @OA\Schema(type="integer", default="1")
     *     ),
     *     @OA\Parameter(name="user_id", in="query", description="Driver id", required=true,
     *           @OA\Schema(type="integer", default="1")
     *     ),
     *     @OA\Parameter(name="transaction_date", in="query", description="Transaction datetime", required=true,
     *           @OA\Schema(type="string", default="m/d/Y H:i:s")
     *     ),
     *     @OA\Parameter(name="location", in="query", description="location", required=true,
     *           @OA\Schema(type="string", default="")
     *     ),
     *     @OA\Parameter(name="state", in="query", description="state", required=true,
     *           @OA\Schema(type="string", default="")
     *     ),
     *     @OA\Parameter(name="fees", in="query", description="fees", required=true,
     *           @OA\Schema(type="number", format="float", default="")
     *     ),
     *     @OA\Parameter(name="item", in="query", description="item", required=true,
     *           @OA\Schema(type="string", default="")
     *     ),
     *     @OA\Parameter(name="unit_price", in="query", description="unit_price", required=true,
     *           @OA\Schema(type="number", format="float", default="")
     *     ),
     *     @OA\Parameter(name="quantity", in="query", description="quantity", required=true,
     *           @OA\Schema(type="number", format="float", default="")
     *     ),
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/FuelingResource")
     *     ),
     * )
     */
    public function update(FuelingUpdateRequest $request, Fueling $fueling)
    {
        $this->authorize('fuel-cards update');

        try {
            $fuelCard = $this->service->update($fueling, $request->validated());

            return FuelingPaginatedResource::make($fuelCard);
        } catch (Exception $exception) {
            Log::error($exception);

            return $this->makeErrorResponse($exception->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param Fueling $fueling
     * @return JsonResponse
     * @OA\Delete(
     *     path="/api/fueling/{fueling}",
     *     tags={"Fueling"}, summary="Delete fueling", operationId="Delete fueling", deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Response(response=204, description="Successful operation",),
     *     @OA\Parameter(name="fueling", in="path", description="fueling id", required=true,
     *         @OA\Schema(type="integer", default="1",)
     *     ),
     * )
     */
    public function destroy(Fueling $fueling): JsonResponse
    {
        $this->authorize('fueling delete');

        try {
            $this->service->destroy($fueling);
        } catch (HasRelatedEntitiesException $exception) {
            return $this->makeErrorResponse(
                $this->getMessageForDestroyFailed($fueling),
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        } catch (Exception $exception) {
            Log::error($exception);

            return $this->makeErrorResponse($exception->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }


        return $this->makeSuccessResponse(null, Response::HTTP_NO_CONTENT);
    }
}
