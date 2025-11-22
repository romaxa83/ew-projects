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
use App\Http\Resources\Fueling\FuelingValidatedResource;
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

class FuelingImportController extends ApiController
{

    protected FuelingService $service;

    public function __construct(FuelingService $service)
    {
        parent::__construct();

        $this->service = $service;
    }

    /**
     * @param IndexFuelingNoValidRequest $request
     * @return AnonymousResourceCollection
     *
     * @OA\Get(
     *     path="/api/fueling-import",
     *     tags={"FuelingImport"},
     *     summary="Get not valid fueling list",
     *     operationId="Get not valid fueling data",
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
     *     @OA\Parameter(name="order_by", in="query", description="Field for sort", required=false,
     *         @OA\Schema(type="string", default="status", enum ={"name"})
     *     ),
     *     @OA\Parameter(name="order_type", in="query", description="Type for sort", required=false,
     *         @OA\Schema(type="string", default="desc", enum ={"asc","desc"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/FuelingValidatedPaginatedResource"),
     *     )
     * )
     */
    public function index(IndexFuelingNoValidRequest $request): AnonymousResourceCollection
    {
        $this->authorize('fueling');

        $cards = Fueling::query()
            ->with('fuelCard', 'driver')
            ->where('valid', false)
            ->orderBy($request->order_by, $request->order_type)
            ->paginate($request->per_page);

        return FuelingValidatedResource::collection($cards);
    }

    /**
     * @param FuelingUpdateRequest $request
     * @param Fueling $fueling
     * @return FuelingValidatedResource|JsonResponse
     * @throws AuthorizationException
     * @OA\Put(
     *     path="/api/fueling-import/{fueling}", tags={"FuelingImport"}, summary="Update import fueling", operationId="Update import fueling", deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/ValidateOnly"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="id", in="path", description="fueling id", required=true,
     *          @OA\Schema(type="integer", default="1",)
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
     *     @OA\Parameter(name="timezone", in="query", description="timezone", required=true,
     *           @OA\Schema(type="string", default="")
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
     *         @OA\JsonContent(ref="#/components/schemas/FuelingValidatedResource")
     *     ),
     * )
     */
    public function update(FuelingUpdateRequest $request, Fueling $fueling)
    {
        $this->authorize('fuel-cards update');

        try {
            $fuelCard = $this->service->updateImport($fueling, $request->validated());

            return FuelingValidatedResource::make($fuelCard);
        } catch (Exception $exception) {
            Log::error($exception);

            return $this->makeErrorResponse($exception->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param Fueling $fueling
     * @return JsonResponse
     * @OA\Delete(
     *     path="/api/fueling-import/{fueling}",
     *     tags={"FuelingImport"}, summary="Delete import fueling", operationId="Delete import fueling", deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="fueling", in="path", description="fueling id", required=true,
     *         @OA\Schema(type="integer", default="1",)
     *     ),
     *     @OA\Response(response=204, description="Successful operation",),
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
