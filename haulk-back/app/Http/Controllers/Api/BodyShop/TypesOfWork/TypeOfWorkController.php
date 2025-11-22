<?php

namespace App\Http\Controllers\Api\BodyShop\TypesOfWork;

use App\Http\Controllers\ApiController;
use App\Http\Requests\BodyShop\TypesOfWork\TypeOfWorkIndexRequest;
use App\Http\Requests\BodyShop\TypesOfWork\TypeOfWorkRequest;
use App\Http\Requests\SearchRequest;
use App\Http\Resources\BodyShop\TypesOfWork\TypeOfWorkPaginateResource;
use App\Http\Resources\BodyShop\TypesOfWork\TypeOfWorkResource;
use App\Http\Resources\BodyShop\TypesOfWork\TypeOfWorkShortListResource;
use App\Models\BodyShop\TypesOfWork\TypeOfWork;
use App\Services\BodyShop\TypesOfWork\TypeOfWorkService;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Throwable;

class TypeOfWorkController extends ApiController
{
    protected TypeOfWorkService $service;

    public function __construct(TypeOfWorkService $service)
    {
        parent::__construct();

        $this->service = $service;
    }

    /**
     * @param TypeOfWorkIndexRequest $request
     * @return AnonymousResourceCollection
     *
     * @OA\Get(
     *     path="/api/body-shop/types-of-work",
     *     tags={"Types Of Work Body Shop"},
     *     summary="Get types of work paginated list",
     *     operationId="Get types of work data",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="page", in="query", description="Page number", required=false,
     *          @OA\Schema( type="integer", default="5")
     *     ),
     *     @OA\Parameter(name="per_page", in="query", description="States per page", required=false,
     *          @OA\Schema(type="integer", default="10")
     *     ),
     *     @OA\Parameter(name="q", in="query", description="Scope for filter by name, email, phone", required=false,
     *          @OA\Schema(type="string",default="name",)
     *     ),
     *     @OA\Parameter(name="inventory_id", in="query", description="Filter by inventory id", required=false,
     *          @OA\Schema(type="integer",default="1",)
     *     ),
     *     @OA\Parameter(name="order_by", in="query", description="Field for sort", required=false,
     *          @OA\Schema(type="string", default="status", enum ={"name"})
     *     ),
     *     @OA\Parameter(name="order_type", in="query", description="Type for sort", required=false,
     *          @OA\Schema(type="string", default="desc", enum ={"asc","desc"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/TypeOfWorkPaginate")
     *     ),
     * )
     */
    public function index(TypeOfWorkIndexRequest $request): AnonymousResourceCollection
    {
        $this->authorize('types_of_work');

        $typesOfWork = TypeOfWork::query()
            ->with(['inventories'])
            ->filter($request->validated())
            ->orderBy($request->order_by, $request->order_type)
            ->paginate($request->per_page);

        return TypeOfWorkPaginateResource::collection($typesOfWork);
    }

    /**
     * @param TypeOfWorkRequest $request
     * @return TypeOfWorkResource|JsonResponse
     * @throws AuthorizationException
     * @throws Throwable
     * @OA\Post(path="/api/body-shop/types-of-work", tags={"Types Of Work Body Shop"}, summary="Create Type Of Work", operationId="Create Type Of Work", deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/ValidateOnly"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="name", in="query", description="Type Of Work name", required=true,
     *          @OA\Schema(type="string", default="Work name",)
     *     ),
     *     @OA\Parameter(name="duration", in="query", description="Type Of Work duration", required=true,
     *          @OA\Schema(type="string", default="10:00",)
     *     ),
     *     @OA\Parameter(name="hourly_rate", in="query", description="Type Of Work rate", required=true,
     *          @OA\Schema(type="number", default="10.20",)
     *     ),
     *     @OA\Parameter(name="inventories", in="query", description="Type Of Work inventories", required=true,
     *          @OA\Schema(type="array",
     *              @OA\Items(
     *                  allOf={
     *                      @OA\Schema(
     *                          @OA\Property(property="id", type="integer", description="Type Of Work inventory id"),
     *                          @OA\Property(property="quantity", type="number", description="Type Of Work inventory quantity"),
     *                     )
     *                 }
     *              )
     *          )
     *     ),
     *     @OA\Response(response=201, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/TypeOfWork")
     *     ),
     * )
     */
    public function store(TypeOfWorkRequest $request)
    {
        $this->authorize('types_of_work create');

        try {
            $typeOfWork = $this->service->create($request->dto());

            return TypeOfWorkResource::make($typeOfWork);
        } catch (Exception $exception) {
            Log::error($exception);

            return $this->makeErrorResponse($exception->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     *
     * @OA\Get(
     *     path="/api/body-shop/types-of-work/{typeOfWorkId}",
     *     tags={"Types Of Work Body Shop"},
     *     summary="Get Type Of Work record",
     *     operationId="Get Type Of Work record",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="id", in="path", description="Type Of Work id", required=true,
     *          @OA\Schema( type="integer",)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/TypeOfWork")
     *     ),
     * )
     * @param TypeOfWork $typesOfWork
     * @return TypeOfWorkResource
     * @throws AuthorizationException
     */
    public function show(TypeOfWork $typesOfWork): TypeOfWorkResource
    {
        $this->authorize('types_of_work read');

        return TypeOfWorkResource::make($typesOfWork);
    }

    /**
     * Update the specified resource in storage.
     *
     * @OA\Put(
     *     path="/api/body-shop/types-of-work/{typeOfWorkId}",
     *     tags={"Types Of Work Body Shop"},
     *     summary="Update Type Of Work record",
     *     operationId="Update Type Of Work",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="id", in="path", description="Type Of Work id", required=true,
     *          @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(name="name", in="query", description="Type Of Work name", required=true,
     *          @OA\Schema(type="string", default="Work name",)
     *     ),
     *     @OA\Parameter(name="duration", in="query", description="Type Of Work duration", required=true,
     *          @OA\Schema(type="string", default="10:00",)
     *     ),
     *     @OA\Parameter(name="hourly_rate", in="query", description="Type Of Work rate", required=true,
     *          @OA\Schema(type="number", default="10.20",)
     *     ),
     *     @OA\Parameter(name="inventories", in="query", description="Type Of Work inventories", required=true,
     *          @OA\Schema(type="array",
     *              @OA\Items(
     *                  allOf={
     *                      @OA\Schema(
     *                          @OA\Property(property="id", type="integer", description="Type Of Work inventory id"),
     *                          @OA\Property(property="quantity", type="number", description="Type Of Work inventory quantity"),
     *                     )
     *                 }
     *              )
     *          )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/TypeOfWork")
     *     ),
     * )
     * @param TypeOfWorkRequest $request
     * @param TypeOfWork $typesOfWork
     * @return TypeOfWorkResource|JsonResponse
     * @throws AuthorizationException
     * @throws Throwable
     */
    public function update(TypeOfWorkRequest $request, TypeOfWork $typesOfWork)
    {
        $this->authorize('types_of_work update');

        try {
            $this->service->update($typesOfWork, $request->dto());

            return TypeOfWorkResource::make($typesOfWork);
        } catch (Exception $e) {
            Log::error($e);

            return $this->makeErrorResponse(null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @OA\Delete(
     *     path="/api/body-shop/types-of-work/{typeOfWorkId}",
     *     tags={"Types Of Work Body Shop"},
     *     summary="Delete Type Of Work",
     *     operationId="Delete Type Of Work",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="id", in="path", description="Type Of Work id", required=true,
     *          @OA\Schema(type="integer", default="1",)
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Successful operation",
     *     ),
     * )
     * @param TypeOfWork $typesOfWork
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function destroy(TypeOfWork $typesOfWork): JsonResponse
    {
        $this->authorize('types_of_work delete');

        try {
            $this->service->destroy($typesOfWork);

            return $this->makeSuccessResponse(null, Response::HTTP_NO_CONTENT);
        } catch (Exception $e) {
            Log::error($e);

            return $this->makeErrorResponse(null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param SearchRequest $request
     * @return AnonymousResourceCollection
     *
     * @OA\Get(
     *     path="/api/body-shop/types-of-work/shortlist",
     *     tags={"Types Of Work Body Shop"},
     *     summary="Get Types Of Work short list",
     *     operationId="Get Types Of Work data",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="q", in="query", description="Scope for filter by name", required=false,
     *          @OA\Schema( type="string", default="name",)
     *     ),
     *     @OA\Parameter(name="searchid", in="query", description="Filter by id", required=false,
     *          @OA\Schema( type="integer", default="1",)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/TypeOfWorkShortList"),
     *     )
     * )
     *
     * @return JsonResponse
     *
     * @throws AuthorizationException
     */
    public function shortlist(SearchRequest $request): AnonymousResourceCollection
    {
        $typesOfWork = TypeOfWork::query()
            ->filter($request->validated())
            ->limit(SearchRequest::DEFAULT_LIMIT)
            ->get();

        return TypeOfWorkShortListResource::collection($typesOfWork);
    }
}
