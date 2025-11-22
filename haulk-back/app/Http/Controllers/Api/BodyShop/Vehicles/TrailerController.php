<?php

namespace App\Http\Controllers\Api\BodyShop\Vehicles;

use App\Exceptions\HasRelatedEntitiesException;
use App\Http\Controllers\ApiController;
use App\Http\Requests\BodyShop\Vehicles\Trailers\TrailerIndexRequest;
use App\Http\Requests\BodyShop\Vehicles\Trailers\TrailerRequest;
use App\Http\Requests\Vehicles\SameVinRequest;
use App\Http\Requests\Vehicles\VehicleHistoryRequest;
use App\Http\Resources\BodyShop\History\HistoryListResource;
use App\Http\Resources\BodyShop\History\HistoryPaginatedResource;
use App\Http\Resources\BodyShop\Vehicles\Trailers\TrailerPaginateResource;
use App\Http\Resources\BodyShop\Vehicles\Trailers\TrailerResource;
use App\Http\Resources\Users\UserShortListResource;
use App\Http\Resources\Vehicles\SameVinResource;
use App\Models\Vehicles\Trailer;
use App\Services\Vehicles\TrailerService;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Throwable;

class TrailerController extends ApiController
{
    protected TrailerService $service;

    public function __construct(TrailerService $service)
    {
        parent::__construct();

        $this->service = $service;
        $this->service->setUser(authUser());
    }

    /**
     * @param TrailerIndexRequest $request
     * @return AnonymousResourceCollection
     *
     * @OA\Get(
     *     path="/api/bodys-shop/trailers",
     *     tags={"Trailers Body Shop"},
     *     summary="Get trailers paginated list",
     *     operationId="Get trailers data",
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
     *         @OA\JsonContent(ref="#/components/schemas/TrailerPaginateBS")
     *     ),
     * )
     */
    public function index(TrailerIndexRequest $request): AnonymousResourceCollection
    {
        $this->authorize('trailers');

        $owners = Trailer::query()
            ->select('*')
            ->withBodyShopCompanies()
            ->filter($request->validated())
            ->sort($request->order_by ?? 'id', $request->order_type ?? 'desc')
            ->paginate($request->per_page);

        return TrailerPaginateResource::collection($owners);
    }

    /**
     * @param TrailerRequest $request
     * @return TrailerResource|JsonResponse
     * @throws AuthorizationException
     * @throws Throwable
     * @OA\Post(path="/api/body-shop/trailers", tags={"Trailers Body Shop"}, summary="Create Trailer", operationId="Create Trailer", deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/ValidateOnly"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="vin", in="query", description="Trailer vin number", required=true,
     *          @OA\Schema(type="string", default="DFDFD76SDD76",)
     *     ),
     *     @OA\Parameter(name="unit_number", in="query", description="Trailer Unit number", required=true,
     *          @OA\Schema(type="string", default="34FDK",)
     *     ),
     *     @OA\Parameter(name="make", in="query", description="Trailer make", required=true,
     *          @OA\Schema(type="string", default="Audi",)
     *     ),
     *     @OA\Parameter(name="model", in="query", description="Trailer model", required=true,
     *          @OA\Schema(type="string", default="A3",)
     *     ),
     *     @OA\Parameter(name="year", in="query", description="Trailer year", required=true,
     *          @OA\Schema(type="string", default="2022",)
     *     ),
     *     @OA\Parameter(name="license_plate", in="query", description="Trailer license plate", required=true,
     *          @OA\Schema(type="string", default="ER2342",)
     *     ),
     *     @OA\Parameter(name="notes", in="query", description="Trailer notes", required=false,
     *          @OA\Schema(type="string", default="text notes",)
     *     ),
     *     @OA\Parameter(name="owner_id", in="query", description="Trailer owner id", required=true,
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
    public function store(TrailerRequest $request)
    {
        $this->authorize('trailers create');

        try {
            $user = $this->service->create($request->getDto());

            return TrailerResource::make($user);
        } catch (Exception $exception) {
            Log::error($exception);

            return $this->makeErrorResponse($exception->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     *
     * @OA\Get(
     *     path="/api/body-shop/trailers/{trailerId}",
     *     tags={"Trailers Body Shop"},
     *     summary="Get Trailer record",
     *     operationId="Get Trailer record",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="id", in="path", description="Trailer id", required=true,
     *          @OA\Schema( type="integer",)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/TrailerBS")
     *     ),
     * )
     * @param Trailer $trailer
     * @return TrailerResource
     * @throws AuthorizationException
     */
    public function show(Trailer $trailer): TrailerResource
    {
        $this->authorize('trailers read');

        return TrailerResource::make($trailer);
    }

    /**
     * Update the specified resource in storage.
     *
     * @OA\Post(
     *     path="/api/body-shop/trailers/{trailerId}",
     *     tags={"Trailers Body Shop"},
     *     summary="Update Trailer record",
     *     operationId="Update Trailer",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="id", in="path", description="Trailer id", required=true,
     *          @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(name="vin", in="query", description="Trailer vin number", required=true,
     *          @OA\Schema(type="string", default="DFDFD76SDD76",)
     *     ),
     *     @OA\Parameter(name="unit_number", in="query", description="Trailer Unit number", required=true,
     *          @OA\Schema(type="string", default="34FDK",)
     *     ),
     *     @OA\Parameter(name="make", in="query", description="Trailer make", required=true,
     *          @OA\Schema(type="string", default="Audi",)
     *     ),
     *     @OA\Parameter(name="model", in="query", description="Trailer model", required=true,
     *          @OA\Schema(type="string", default="A3",)
     *     ),
     *     @OA\Parameter(name="year", in="query", description="Trailer year", required=true,
     *          @OA\Schema(type="string", default="2022",)
     *     ),
     *     @OA\Parameter(name="license_plate", in="query", description="Trailer license plate", required=true,
     *          @OA\Schema(type="string", default="ER2342",)
     *     ),
     *     @OA\Parameter(name="notes", in="query", description="Trailer notes", required=false,
     *          @OA\Schema(type="string", default="text notes",)
     *     ),
     *     @OA\Parameter(name="owner_id", in="query", description="Trailer owner id", required=true,
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
     *         @OA\JsonContent(ref="#/components/schemas/TrailerBS")
     *     ),
     * )
     * @param TrailerRequest $request
     * @param Trailer $trailer
     * @return TrailerResource|JsonResponse
     * @throws AuthorizationException
     * @throws Throwable
     */
    public function update(TrailerRequest $request, Trailer $trailer)
    {
        $this->authorize('trailers update');

        if ($trailer->getCompanyId() !== request()->user()->getCompanyId()) {
            return $this->makeErrorResponse('', Response::HTTP_FORBIDDEN);
        }

        try {
            $this->service->update($trailer, $request->getDto());

            return TrailerResource::make($trailer->refresh());
        } catch (Exception $e) {
            Log::error($e);

            return $this->makeErrorResponse(null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @OA\Delete(
     *     path="/api/body-shop/trailers/{trailerId}",
     *     tags={"Trailers Body Shop"},
     *     summary="Delete Trailer",
     *     operationId="Delete Trailer",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="id", in="path", description="Trailer id", required=true,
     *          @OA\Schema(type="integer", default="1",)
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Successful operation",
     *     ),
     * )
     * @param Trailer $truck
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function destroy(Trailer $trailer)
    {
        $this->authorize('trucks delete');

        if ($trailer->getCompanyId() !== request()->user()->getCompanyId()) {
            return $this->makeErrorResponse('', Response::HTTP_FORBIDDEN);
        }

        try {
            $this->service->destroy($trailer);

            return $this->makeSuccessResponse(null, Response::HTTP_NO_CONTENT);
        } catch (HasRelatedEntitiesException $e) {
            if ($trailer->hasRelatedDeletedOrders() && $trailer->hasRelatedOpenOrders()) {
                return $this->makeErrorResponse(
                    trans(
                        'This trailer is used in the <a href=":open_orders">open</a> and <a href=":deleted_orders">deleted</a> orders. Please delete orders permanently first.',
                        [
                            'open_orders' => str_replace('{id}', $trailer->id, config('frontend.bs_open_orders_with_trailer_filter_url')),
                            'deleted_orders' => str_replace('{id}', $trailer->id, config('frontend.bs_deleted_orders_with_trailer_filter_url')),
                        ],
                    ),
                    Response::HTTP_UNPROCESSABLE_ENTITY
                );
            }
            if ($trailer->hasRelatedDeletedOrders()) {
                return $this->makeErrorResponse(
                    trans(
                        'This trailer is used in the <a href=":deleted_orders">deleted</a> orders. Please delete orders permanently first.',
                        [
                            'deleted_orders' => str_replace('{id}', $trailer->id, config('frontend.bs_deleted_orders_with_trailer_filter_url')),
                        ],
                    ),
                    Response::HTTP_UNPROCESSABLE_ENTITY
                );
            }
            return $this->makeErrorResponse(
                trans(
                    'This trailer is used in the <a href=":open_orders">open</a> orders. Please delete orders permanently first.',
                    [
                        'open_orders' => str_replace('{id}', $trailer->id, config('frontend.bs_open_orders_with_trailer_filter_url')),
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
     *     path="/api/body-shop/trailers/same-vin",
     *     tags={"Trailers Body Shop"},
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
            $this->service->getTrailersWithVin($request->vin, $request->id ?? null, true)
        );
    }

    /**
     *
     * @OA\Delete(
     *     path="/api/body-shop/trailers/{trailerId}/attachments/{attachmentId}",
     *     tags={"Trailers Body Shop"},
     *     summary="Delete attachment from trailer",
     *     operationId="Delete attachment",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Response(response=204, description="Successful operation"),
     * )
     */
    public function deleteAttachment(Trailer $trailer, int $id)
    {
        $this->authorize('trailers update');

        try {
            $this->service->deleteAttachment($trailer, $id);

            return $this->makeSuccessResponse(null, Response::HTTP_NO_CONTENT);
        } catch (Exception $e) {
            Log::error($e->getTraceAsString());
            return $this->makeErrorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get trailer history
     *
     * @param Trailer $trailer
     * @return AnonymousResourceCollection|JsonResponse
     *
     * @throws AuthorizationException
     * @OA\Get(
     *     path="/api/body-shop/trailers/{trailerId}/history",
     *     tags={"Trailers Body Shop"},
     *     summary="Get trailer history",
     *     operationId="Get trailer history",
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
    public function history(Trailer $trailer)
    {
        $this->authorize('trailers read');

        try {
            return HistoryListResource::collection(
                $this->service->getHistoryShort($trailer, true)
            );
        } catch (Exception $e) {
            Log::error($e->getTraceAsString());
            return $this->makeErrorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get trailer history detailed paginate
     *
     * @param Trailer $trailer
     * @param VehicleHistoryRequest $request
     * @return AnonymousResourceCollection|JsonResponse
     *
     * @throws AuthorizationException
     * @OA\Get(
     *     path="/api/body-shop/trailers/{trailerId}/history-detailed",
     *     tags={"Trailers Body Shop"},
     *     summary="Get trailer history detailed",
     *     operationId="Get trailer history detailed",
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
    public function historyDetailed(Trailer $trailer, VehicleHistoryRequest $request)
    {
        $this->authorize('trailers read');

        try {
            return HistoryPaginatedResource::collection($this->service->getHistoryDetailed($trailer, $request, true));
        } catch (Exception $e) {
            Log::error($e->getTraceAsString());
            return $this->makeErrorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get trailer history users
     *
     * @param Trailer $trailer
     * @return AnonymousResourceCollection
     *
     * @throws AuthorizationException
     * @OA\Get(
     *     path="/api/body-shop/trailers/{trailerId}/history-users-list",
     *     tags={"Trailers Body Shop"},
     *     summary="Get list users changes trailer",
     *     operationId="Get list users changes trailer",
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
    public function historyUsers(Trailer $trailer): AnonymousResourceCollection
    {
        $this->authorize('trailers read');

        return UserShortListResource::collection($this->service->getHistoryUsers($trailer, true));
    }
}
