<?php

namespace App\Http\Controllers\Api\V1\Vehicles\Trailer;

use App\Foundations\Modules\Permission\Permissions as Permission;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Vehicles\Trailer\TrailerFilterRequest;
use App\Http\Requests\Vehicles\Trailer\TrailerRequest;
use App\Http\Resources\Vehicles\Trailer\TrailerPaginationResource;
use App\Http\Resources\Vehicles\Trailer\TrailerResource;
use App\Models\Vehicles\Trailer;
use App\Repositories\Vehicles\TrailerRepository;
use App\Services\Vehicles\TrailerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Response;

class CrudController extends ApiController
{
    public function __construct(
        protected TrailerRepository $repo,
        protected TrailerService $service,
    )
    {}

    /**
     * @OA\Get(
     *     path="/api/v1/trailers",
     *     tags={"Vehicles trailer"},
     *     security={{"Basic": {}}},
     *     summary="Get trailers pagination",
     *     operationId="GetTrailersPagination",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *
     *     @OA\Parameter(ref="#/components/parameters/Page"),
     *     @OA\Parameter(ref="#/components/parameters/PerPage"),
     *
     *     @OA\Parameter(name="search", in="query", required=false,
     *         description="Scope for search by vin, unit number, licance plate, temporary plate",
     *         @OA\Schema( type="string", example="name",)
     *     ),
     *     @OA\Parameter(name="tag_id", in="query", description="Tag id", required=false,
     *         @OA\Schema( type="integer", example="1",)
     *     ),
     *     @OA\Parameter(name="customer_id", in="query", description="Customer id", required=false,
     *          @OA\Schema( type="integer", example="1",)
     *     ),
     *
     *     @OA\Response(response=200, description="Trailer paginated data",
     *         @OA\JsonContent(ref="#/components/schemas/TrailerPaginationResource")
     *     ),
     *
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function index(TrailerFilterRequest $request): ResourceCollection
    {
        $this->authorize(Permission\Trailer\TrailerReadPermission::KEY);

        return TrailerPaginationResource::collection(
            $this->repo->customPagination(
                relation: ['customer', 'comments', 'tags'],
                filters: $request->validated()
            )
        );
    }

    /**
     * @OA\Post(
     *     path="/api/v1/trailers",
     *     tags={"Vehicles trailer"},
     *     security={{"Basic": {}}},
     *     summary="Create trailers",
     *     operationId="CreateTrailers",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(ref="#/components/parameters/ValidateOnly"),
     *
     *     @OA\RequestBody(required=true,
     *         @OA\JsonContent(ref="#/components/schemas/TrailerRequest")
     *     ),
     *
     *     @OA\Response(response=201, description="Trailer data",
     *         @OA\JsonContent(ref="#/components/schemas/TrailerResource")
     *     ),
     *
     *     @OA\Response(response="422", description="Validation", @OA\JsonContent(ref="#/components/schemas/ValidationErrors")),
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function store(TrailerRequest $request): TrailerResource|JsonResponse
    {
        $this->authorize(Permission\Trailer\TrailerCreatePermission::KEY);

        return TrailerResource::make(
            $this->service->create($request->getDto())
        );
    }

    /**
     * @OA\Post(
     *     path="/api/v1/trailers/{id}",
     *     tags={"Vehicles trailer"},
     *     security={{"Basic": {}}},
     *     summary="Update trailer",
     *     operationId="UpadteTrailer",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(ref="#/components/parameters/ValidateOnly"),
     *
     *     @OA\Parameter(ref="#/components/parameters/IDPath"),
     *
     *     @OA\RequestBody(required=true,
     *         @OA\JsonContent(ref="#/components/schemas/TrailerRequest")
     *     ),
     *
     *     @OA\Response(response=201, description="Trailer data",
     *         @OA\JsonContent(ref="#/components/schemas/TrailerResource")
     *     ),
     *
     *     @OA\Response(response="422", description="Validation", @OA\JsonContent(ref="#/components/schemas/ValidationErrors")),
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="404", description="Not Found", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function update(TrailerRequest $request): TrailerResource|JsonResponse
    {
        $this->authorize(Permission\Trailer\TrailerUpdatePermission::KEY);

        return TrailerResource::make(
            $this->service->update($request->getModel(), $request->getDto())
        );
    }

    /**
     * @OA\Get(
     *     path="/api/v1/trailers/{id}",
     *     tags={"Vehicles trailer"},
     *     security={{"Basic": {}}},
     *     summary="Get info about trailer",
     *     operationId="GetInfoAboutTrailer",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *
     *     @OA\Parameter(ref="#/components/parameters/IDPath"),
     *
     *     @OA\Response(response=200, description="Trailer data",
     *         @OA\JsonContent(ref="#/components/schemas/TrailerResource")
     *     ),
     *
     *      @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *      @OA\Response(response="404", description="Not Found", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *      @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function show($id): TrailerResource
    {
        $this->authorize(Permission\Trailer\TrailerReadPermission::KEY);

        return TrailerResource::make(
            $this->repo->getBy(['id' => $id], withException: true,
                exceptionMessage: __("exceptions.vehicles.trailer.not_found")
            )
        );
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/trailers/{id}",
     *     tags={"Vehicles trailer"},
     *     security={{"Basic": {}}},
     *     summary="Delete trailer",
     *     operationId="DeleteTrailer",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/IDPath"),
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *
     *     @OA\Response(response=204, description="Successful delete"),
     *
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="404", description="Not Found", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function delete($id): JsonResponse
    {
        $this->authorize(Permission\Trailer\TrailerDeletePermission::KEY);

        /** @var $model Trailer */
        $model = $this->repo->getBy(['id' => $id],
            withException: true,
            exceptionMessage: __("exceptions.vehicles.trailer.not_found")
        );

        if ($model->hasRelatedOpenOrders() || $model->hasRelatedDeletedOrders()) {
            return $this->errorJsonMessage($this->getMessageForDeleteFailed($model),
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $this->service->delete($model);

        return $this->successJsonMessage(null, Response::HTTP_NO_CONTENT);
    }

    protected function getMessageForDeleteFailed(Trailer $model): string
    {
         if($model->hasRelatedOpenOrders() && $model->hasRelatedDeletedOrders()){
             $openOrderLink = str_replace('{id}', $model->id, config('routes.front.bs_open_orders_with_trailer_filter_url'));
             $deleteOrderLink = str_replace('{id}', $model->id, config('routes.front.bs_deleted_orders_with_trailer_filter_url'));

             return __("exceptions.vehicles.trailer.has_open_and_deleted_orders", [
                 'open_orders' => $openOrderLink,
                 'deleted_orders' => $deleteOrderLink,
             ]);
         } elseif ($model->hasRelatedDeletedOrders()){
             $deleteOrderLink = str_replace('{id}', $model->id, config('routes.front.bs_deleted_orders_with_trailer_filter_url'));

             return __("exceptions.vehicles.trailer.has_deleted_orders", [
                 'deleted_orders' => $deleteOrderLink,
             ]);
         }

        $openOrderLink = str_replace('{id}', $model->id, config('routes.front.bs_open_orders_with_trailer_filter_url'));

        return __("exceptions.vehicles.trailer.has_open_orders", [
            'open_orders' => $openOrderLink,
        ]);
    }
}

