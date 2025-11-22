<?php

namespace App\Http\Controllers\Api\BodyShop\Vehicles;

use App\Exceptions\HasRelatedEntitiesException;
use App\Http\Controllers\ApiController;
use App\Http\Requests\BodyShop\Vehicles\Trucks\TruckIndexRequest;
use App\Http\Requests\BodyShop\Vehicles\Trucks\TruckRequest;
use App\Http\Requests\Vehicles\SameVinRequest;
use App\Http\Requests\Vehicles\VehicleHistoryRequest;
use App\Http\Resources\BodyShop\History\HistoryListResource;
use App\Http\Resources\BodyShop\History\HistoryPaginatedResource;
use App\Http\Resources\BodyShop\Vehicles\Trucks\TruckPaginateResource;
use App\Http\Resources\BodyShop\Vehicles\Trucks\TruckResource;
use App\Http\Resources\Users\UserShortListResource;
use App\Http\Resources\Vehicles\SameVinResource;
use App\Models\Vehicles\Truck;
use App\Services\Vehicles\TruckService;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Throwable;

class TruckController extends ApiController
{
    protected TruckService $service;

    public function __construct(TruckService $service)
    {
        parent::__construct();

        $this->service = $service;
        $this->service->setUser(authUser());
    }

    /**
     * @param TruckIndexRequest $request
     * @return AnonymousResourceCollection
     *
     * @OA\Get(
     *     path="/api/body-shop/trucks",
     *     tags={"Trucks Body Shop"},
     *     summary="Get trucks paginated list",
     *     operationId="Get trucks data",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="page", in="query", description="Page number", required=false,
     *          @OA\Schema( type="integer", default="5")
     *     ),
     *     @OA\Parameter(name="per_page", in="query", description="States per page", required=false,
     *          @OA\Schema( type="integer", default="10")
     *     ),
     *     @OA\Parameter(  name="q", in="query", description="Scope for search by vin, unit number, licance plate, temporary plate", required=false,
     *          @OA\Schema( type="string", default="name",)
     *     ),
     *     @OA\Parameter(name="owner_id", in="query", description="Vehicle owner id", required=false,
     *          @OA\Schema(type="integer", default="1",)
     *     ),
     *     @OA\Parameter(name="driver_id", in="query", description="Vehicle driver id", required=false,
     *          @OA\Schema( type="integer", default="1", )
     *     ),
     *     @OA\Parameter(name="tag_id", in="query", description="Tag id", required=false,
     *          @OA\Schema( type="integer", default="1",)
     *     ),
     *     @OA\Parameter(name="order_by", in="query", description="Field for sort", required=false,
     *          @OA\Schema(type="string", default="status", enum ={"company_name"})
     *     ),
     *     @OA\Parameter(name="order_type", in="query", description="Type for sort", required=false,
     *          @OA\Schema(type="string", default="desc", enum ={"asc","desc"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/TruckPaginateBS")
     *     ),
     * )
     */
    public function index(TruckIndexRequest $request): AnonymousResourceCollection
    {
        $this->authorize('trucks');

        $owners = Truck::query()
            ->withBodyShopCompanies()
            ->filter($request->validated())
            ->orderBy('id', 'desc')
            ->paginate($request->per_page);

        return TruckPaginateResource::collection($owners);
    }

    /**
     * @param TruckRequest $request
     * @return TruckResource|JsonResponse
     * @throws AuthorizationException
     * @throws Throwable
     * @OA\Post(path="/api/body-shop/trucks", tags={"Trucks Body Shop"}, summary="Create Truck", operationId="Create Truck", deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/ValidateOnly"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="vin", in="query", description="Truck vin number", required=true,
     *          @OA\Schema(type="string", default="DFDFD76SDD76",)
     *     ),
     *     @OA\Parameter(name="unit_number", in="query", description="Truck Unit number", required=true,
     *          @OA\Schema(type="string", default="34FDK",)
     *     ),
     *     @OA\Parameter(name="make", in="query", description="Truck make", required=true,
     *          @OA\Schema(type="string", default="Audi",)
     *     ),
     *     @OA\Parameter(name="model", in="query", description="Truck model", required=true,
     *          @OA\Schema(type="string", default="A3",)
     *     ),
     *     @OA\Parameter(name="year", in="query", description="Truck year", required=true,
     *          @OA\Schema(type="string", default="2022",)
     *     ),
     *     @OA\Parameter(name="type", in="query", description="Truck type", required=true,
     *          @OA\Schema(type="integer", default="1",)
     *     ),
     *     @OA\Parameter(name="license_plate", in="query", description="Truck license plate", required=true,
     *          @OA\Schema(type="string", default="ER2342",)
     *     ),
     *     @OA\Parameter(name="notes", in="query", description="Truck notes", required=false,
     *          @OA\Schema(type="string", default="text notes",)
     *     ),
     *     @OA\Parameter(name="owner_id", in="query", description="Truck owner id", required=true,
     *          @OA\Schema(type="integer", default="1",)
     *     ),
     *     @OA\Parameter(name="tags", in="query", description="Tags list", required=false,
     *          @OA\Schema(type="array",
     *              @OA\Items(anyOf={@OA\Schema(type="integer")})
     *          )
     *     ),
     *     @OA\Parameter(name="attachment_files", in="query", description="Attachments list", required=false),
     *     @OA\Parameter(name="color", in="query", description="Color", required=false,
     *          @OA\Schema(type="string", default="red",)
     *     ),
     *     @OA\Parameter(name="gvwr", in="query", description="GVWR", required=false,
     *          @OA\Schema(type="number", example="10",)
     *     ),
     *     @OA\Response(response=201, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/TruckBS")
     *     ),
     * )
     */
    public function store(TruckRequest $request)
    {
        $this->authorize('trucks create');

        try {
            $user = $this->service->create($request->getDto());

            return TruckResource::make($user);
        } catch (Exception $exception) {
            Log::error($exception);

            return $this->makeErrorResponse($exception->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     *
     * @OA\Get(
     *     path="/api/body-shop/trucks/{truckid}",
     *     tags={"Trucks Body Shop"},
     *     summary="Get truck record",
     *     operationId="Get truck record",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="id", in="path", description="Truck id", required=true,
     *          @OA\Schema( type="integer",)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/TruckBS")
     *     ),
     * )
     * @param Truck $truck
     * @return TruckResource
     * @throws AuthorizationException
     */
    public function show(Truck $truck): TruckResource
    {
        $this->authorize('trucks read');

        return TruckResource::make($truck);
    }

    /**
     * Update the specified resource in storage.
     *
     * @OA\Post(
     *     path="/api/body-shop/trucks/{truckId}",
     *     tags={"Trucks Body Shop"},
     *     summary="Update truck record",
     *     operationId="Update truck",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="id", in="path", description="Truck id", required=true,
     *          @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(name="vin", in="query", description="Truck vin number", required=true,
     *          @OA\Schema(type="string", default="DFDFD76SDD76",)
     *     ),
     *     @OA\Parameter(name="unit_number", in="query", description="Truck Unit number", required=true,
     *          @OA\Schema(type="string", default="34FDK",)
     *     ),
     *     @OA\Parameter(name="make", in="query", description="Truck make", required=true,
     *          @OA\Schema(type="string", default="Audi",)
     *     ),
     *     @OA\Parameter(name="model", in="query", description="Truck model", required=true,
     *          @OA\Schema(type="string", default="A3",)
     *     ),
     *     @OA\Parameter(name="year", in="query", description="Truck year", required=true,
     *          @OA\Schema(type="string", default="2022",)
     *     ),
     *     @OA\Parameter(name="type", in="query", description="Truck type", required=true,
     *          @OA\Schema(type="integer", default="1",)
     *     ),
     *     @OA\Parameter(name="license_plate", in="query", description="Truck license plate", required=true,
     *          @OA\Schema(type="string", default="ER2342",)
     *     ),
     *     @OA\Parameter(name="notes", in="query", description="Truck notes", required=false,
     *          @OA\Schema(type="string", default="text notes",)
     *     ),
     *     @OA\Parameter(name="owner_id", in="query", description="Truck owner id", required=true,
     *          @OA\Schema(type="integer", default="1",)
     *     ),
     *     @OA\Parameter(name="tags", in="query", description="Tags list", required=false,
     *          @OA\Schema(type="array",
     *              @OA\Items(anyOf={@OA\Schema(type="integer")})
     *          )
     *     ),
     *     @OA\Parameter(name="attachment_files", in="query", description="Attachments list", required=false),
     *     @OA\Parameter(name="color", in="query", description="Color", required=false,
     *          @OA\Schema(type="string", default="red",)
     *     ),
     *     @OA\Parameter(name="gvwr", in="query", description="GVWR", required=false,
     *          @OA\Schema(type="number", example="10",)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/TruckBS")
     *     ),
     * )
     * @param TruckRequest $request
     * @param Truck $truck
     * @return TruckResource|JsonResponse
     * @throws AuthorizationException
     * @throws Throwable
     */
    public function update(TruckRequest $request, Truck $truck)
    {
        $this->authorize('trucks update');

        if ($truck->getCompanyId() !== $request->user()->getCompanyId()) {
            return $this->makeErrorResponse('', Response::HTTP_FORBIDDEN);
        }

        try {
            $this->service->update($truck, $request->getDto());

            return TruckResource::make($truck->refresh());
        } catch (Exception $e) {
            Log::error($e);

            return $this->makeErrorResponse(null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @OA\Delete(
     *     path="/api/body-shop/trucks/{truckId}",
     *     tags={"Trucks Body Shop"},
     *     summary="Delete truck",
     *     operationId="Delete truck",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="id", in="path", description="Truck id", required=true,
     *          @OA\Schema(type="integer", default="1",)
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Successful operation",
     *     ),
     * )
     * @param Truck $truck
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function destroy(Truck $truck)
    {
        $this->authorize('trucks delete');

        if ($truck->getCompanyId() !== request()->user()->getCompanyId()) {
            return $this->makeErrorResponse('', Response::HTTP_FORBIDDEN);
        }

        try {
            $this->service->destroy($truck);

            return $this->makeSuccessResponse(null, Response::HTTP_NO_CONTENT);
        } catch (HasRelatedEntitiesException $e) {
            if ($truck->hasRelatedDeletedOrders() && $truck->hasRelatedOpenOrders()) {
                return $this->makeErrorResponse(
                    trans(
                        'This truck is used in the <a href=":open_orders">open</a> and <a href=":deleted_orders">deleted</a> orders. Please delete orders permanently first.',
                        [
                            'open_orders' => str_replace('{id}', $truck->id, config('frontend.bs_open_orders_with_truck_filter_url')),
                            'deleted_orders' => str_replace('{id}', $truck->id, config('frontend.bs_deleted_orders_with_truck_filter_url')),
                        ],
                    ),
                    Response::HTTP_UNPROCESSABLE_ENTITY
                );
            }
            if ($truck->hasRelatedDeletedOrders()) {
                return $this->makeErrorResponse(
                    trans(
                        'This truck is used in the <a href=":deleted_orders">deleted</a> orders. Please delete orders permanently first.',
                        [
                            'deleted_orders' => str_replace('{id}', $truck->id, config('frontend.bs_deleted_orders_with_truck_filter_url')),
                        ],
                    ),
                    Response::HTTP_UNPROCESSABLE_ENTITY
                );
            }
            return $this->makeErrorResponse(
                trans(
                    'This truck is used in the <a href=":open_orders">open</a> orders. Please delete orders permanently first.',
                    [
                        'open_orders' => str_replace('{id}', $truck->id, config('frontend.bs_open_orders_with_truck_filter_url')),
                    ],
                ),
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        } catch (Exception $e) {
            Log::error($e);

            return $this->makeErrorResponse(null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param SameVinRequest $request
     * @return AnonymousResourceCollection
     *
     * @OA\Get(
     *     path="/api/body-shop/trucks/same-vin",
     *     tags={"Trucks Body Shop"},
     *     summary="Get vehicles with the same vehicle vin",
     *     operationId="Get vehicles with the same vehicle vin",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(
     *          name="id",
     *          in="query",
     *          description="Current Vehicle ID",
     *          required=false,
     *          @OA\Schema(
     *              type="integer",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="vin",
     *          in="query",
     *          description="",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *          )
     *     ),
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/SameVinResource")
     *     ),
     * )
     */
    public function sameVin(SameVinRequest $request)
    {
        return SameVinResource::collection(
            $this->service->getTrucksWithVin($request->vin, $request->id ?? null, true)
        );
    }

    /**
     *
     * @OA\Delete(
     *     path="/api/body-shop/trucks/{truckId}/attachments/{attachmentId}",
     *     tags={"Trucks Body Shop"},
     *     summary="Delete attachment from truck",
     *     operationId="Delete attachment",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Response(response=204, description="Successful operation"),
     * )
     */
    public function deleteAttachment(Truck $truck, int $id)
    {
        $this->authorize('trucks update');

        try {
            $this->service->deleteAttachment($truck, $id);

            return $this->makeSuccessResponse(null, Response::HTTP_NO_CONTENT);
        } catch (Exception $e) {
            Log::error($e->getTraceAsString());
            return $this->makeErrorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get truck history
     *
     * @param Truck $truck
     * @return AnonymousResourceCollection|JsonResponse
     *
     * @throws AuthorizationException
     * @OA\Get(
     *     path="/api/body-shop/trucks/{truckId}/history",
     *     tags={"Trucks Body Shop"},
     *     summary="Get truck history",
     *     operationId="Get truck history",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/HistoryListResourceBS")
     *     ),
     * )
     */
    public function history(Truck $truck)
    {
        $this->authorize('trucks read');

        try {
            return HistoryListResource::collection(
                $this->service->getHistoryShort($truck, true)
            );
        } catch (Exception $e) {
            Log::error($e->getTraceAsString());
            return $this->makeErrorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get truck history detailed paginate
     *
     * @param Truck $truck
     * @param VehicleHistoryRequest $request
     * @return AnonymousResourceCollection|JsonResponse
     *
     * @throws AuthorizationException
     * @OA\Get(
     *     path="/api/body-shop/trucks/{truckId}/history-detailed",
     *     tags={"Trucks Body Shop"},
     *     summary="Get truck history detailed",
     *     operationId="Get truck history detailed",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="dates_range", in="query", description="06/06/2021 - 06/14/2021", required=false,
     *          @OA\Schema(type="string",)
     *     ),
     *     @OA\Parameter(name="user_id", in="query", description="user_id", required=false,
     *          @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter( name="page", in="query", description="page", required=false,
     *          @OA\Schema(type="integer", default="1")
     *     ),
     *     @OA\Parameter( name="per_page", in="query", description="per page", required=false,
     *          @OA\Schema(type="integer", default="10")
     *     ),
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/HistoryPaginatedResourceBS")
     *     ),
     * )
     */
    public function historyDetailed(Truck $truck, VehicleHistoryRequest $request)
    {
        $this->authorize('trucks read');

        try {
            return HistoryPaginatedResource::collection($this->service->getHistoryDetailed($truck, $request, true));
        } catch (Exception $e) {
            Log::error($e->getTraceAsString());
            return $this->makeErrorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get truck history users
     *
     * @param Truck $truck
     * @return AnonymousResourceCollection
     *
     * @throws AuthorizationException
     * @OA\Get(
     *     path="/api/body-shop/trucks/{truckId}/history-users-list",
     *     tags={"Trucks Body Shop"},
     *     summary="Get list users changes truck",
     *     operationId="Get list users changes truck",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/UserShortList")
     *     ),
     * )
     */
    public function historyUsers(Truck $truck): AnonymousResourceCollection
    {
        $this->authorize('trucks read');

        return UserShortListResource::collection($this->service->getHistoryUsers($truck, true));
    }
}
