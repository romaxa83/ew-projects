<?php

namespace App\Http\Controllers\Api\V1\TypeOfWorks;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Tags\TagRequest;
use App\Http\Requests\TypeOfWorks\TypeOfWorkFilterRequest;
use App\Http\Requests\TypeOfWorks\TypeOfWorkRequest;
use App\Http\Requests\TypeOfWorks\TypeOfWorkShortListRequest;
use App\Http\Resources\Tags\TagResource;
use App\Foundations\Modules\Permission\Permissions as Permission;
use App\Http\Resources\TypeOfWorks\TypeOfWorkInventoryResource;
use App\Http\Resources\TypeOfWorks\TypeOfWorkPaginationResource;
use App\Http\Resources\TypeOfWorks\TypeOfWorkResource;
use App\Http\Resources\TypeOfWorks\TypeOfWorkShortListResource;
use App\Models\Tags\Tag;
use App\Models\TypeOfWorks\TypeOfWork;
use App\Repositories\TypeOfWorks\TypeOfWorkRepository;
use App\Services\TypeOfWorks\TypeOfWorkService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Response;

class CrudController extends ApiController
{
    public function __construct(
        protected TypeOfWorkRepository $repo,
        protected TypeOfWorkService $service,
    )
    {}

    /**
     * @OA\Get(
     *     path="/api/v1/types-of-work",
     *     tags={"Types Of Work"},
     *     security={{"Basic": {}}},
     *     summary="Get types of work paginated list",
     *     operationId="GetTypesOfWorkPaginatedList",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(ref="#/components/parameters/ValidateOnly"),
     *
     *     @OA\Parameter(ref="#/components/parameters/Page"),
     *     @OA\Parameter(ref="#/components/parameters/PerPage"),
     *
     *     @OA\Parameter(ref="#/components/parameters/OrderType"),
     *     @OA\Parameter(name="order_by", in="query", description="Field for sort", required=false,
     *         @OA\Schema(type="string", default="status", enum ={"name"})
     *     ),
     *
     *     @OA\Parameter(name="search", in="query", required=false,
     *         description="Scope for filter by name",
     *         @OA\Schema(type="string", default="null",)
     *     ),
     *     @OA\Parameter(name="inventory_id", in="query", description="Filter by inventory id", required=false,
     *         @OA\Schema(type="integer",default="1",)
     *     ),
     *
     *     @OA\Response(response=200, description="Type of works data",
     *         @OA\JsonContent(ref="#/components/schemas/TypeOfWorkPagination")
     *     ),
     *
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function index(TypeOfWorkFilterRequest $request): ResourceCollection
    {
        $this->authorize(Permission\TypeOfWork\TypeOfWorkReadPermission::KEY);

        return TypeOfWorkPaginationResource::collection(
            $this->repo->customPagination($request->validated(), ['inventories'])
        );
    }

    /**
     * @OA\Post(
     *     path="/api/v1/types-of-work",
     *     tags={"Types Of Work"},
     *     security={{"Basic": {}}},
     *     summary="Create Type Of Work",
     *     operationId="CreateTypeOfWork",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(ref="#/components/parameters/ValidateOnly"),
     *
     *     @OA\RequestBody(required=true,
     *         @OA\JsonContent(ref="#/components/schemas/TypeOfWorkRequest")
     *     ),
     *
     *     @OA\Response(response=201, description="Type of work data",
     *         @OA\JsonContent(ref="#/components/schemas/TypeOfWorkResource")
     *     ),
     *
     *     @OA\Response(response="422", description="Validation", @OA\JsonContent(ref="#/components/schemas/ValidationErrors")),
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function store(TypeOfWorkRequest $request): TypeOfWorkResource|JsonResponse
    {
        $this->authorize(Permission\TypeOfWork\TypeOfWorkCreatePermission::KEY);

        return TypeOfWorkResource::make(
            $this->service->create($request->dto())
        );
    }

    /**
     * @OA\Put(
     *     path="/api/v1/types-of-work/{id}",
     *     tags={"Types Of Work"},
     *     security={{"Basic": {}}},
     *     summary="Update Type Of Work",
     *     operationId="UpdateTypeOfWork",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(ref="#/components/parameters/ValidateOnly"),
     *
     *     @OA\RequestBody(required=true,
     *         @OA\JsonContent(ref="#/components/schemas/TypeOfWorkRequest")
     *     ),
     *
     *     @OA\Response(response=200, description="Type of work data",
     *         @OA\JsonContent(ref="#/components/schemas/TypeOfWorkResource")
     *     ),
     *
     *     @OA\Response(response="422", description="Validation", @OA\JsonContent(ref="#/components/schemas/ValidationErrors")),
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="404", description="Not Found", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function update(TypeOfWorkRequest $request, $id): TypeOfWorkResource|JsonResponse
    {
        $this->authorize(Permission\TypeOfWork\TypeOfWorkUpdatePermission::KEY);

        /** @var $model TypeOfWork */
        $model = $this->repo->getBy(['id' => $id],['inventories'],
            withException: true,
            exceptionMessage: __("exceptions.type_of_works.not_found")
        );

        return TypeOfWorkResource::make(
            $this->service->update($model, $request->dto())
        );
    }

    /**
     * @OA\Get(
     *     path="/api/v1/types-of-work/{id}",
     *     tags={"Types Of Work"},
     *     security={{"Basic": {}}},
     *     summary="Get Type Of Work record",
     *     operationId="GetTypeOfWorkRecord",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *
     *     @OA\Parameter(ref="#/components/parameters/IDPath"),
     *
     *     @OA\Response(response=200, description="Type of work data",
     *         @OA\JsonContent(ref="#/components/schemas/TypeOfWorkResource")
     *     ),
     *
     *      @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *      @OA\Response(response="404", description="Not Found", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *      @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function show($id): TypeOfWorkResource
    {
        $this->authorize(Permission\TypeOfWork\TypeOfWorkReadPermission::KEY);

        return TypeOfWorkResource::make(
            $this->repo->getBy(['id' => $id], withException: true,
                exceptionMessage: __("exceptions.type_of_works.not_found")
            )
        );
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/types-of-work/{id}",
     *     tags={"Types Of Work"},
     *     security={{"Basic": {}}},
     *     summary="Delete type of work",
     *     operationId="DeleteTypeOfWork",
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
        $this->authorize(Permission\TypeOfWork\TypeOfWorkDeletePermission::KEY);

        /** @var $model TypeOfWork */
        $model = $this->repo->getBy(['id' => $id], withException: true,
            exceptionMessage: __("exceptions.type_of_works.not_found")
        );

        $this->service->delete($model);

        return $this->successJsonMessage(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/types-of-work/shortlist",
     *     tags={"Types Of Work"},
     *     security={{"Basic": {}}},
     *     summary="Get type pf work short list",
     *     operationId="GetTypeOfWorkShortlist",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *
     *     @OA\Parameter(ref="#/components/parameters/ID"),
     *     @OA\Parameter(ref="#/components/parameters/Limit"),
     *
     *     @OA\Parameter(name="search", in="query", description="Scope for filter by name, email, phone", required=false,
     *          @OA\Schema(type="string", default="name",)
     *     ),
     *
     *     @OA\Response(response=200, description="Type of work data",
     *         @OA\JsonContent(ref="#/components/schemas/TypeOfWorkShortListResource"),
     *     ),
     *
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="422", description="Not Found", @OA\JsonContent(ref="#/components/schemas/ValidationErrors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function shortlist(TypeOfWorkShortListRequest $request): AnonymousResourceCollection
    {
        $this->authorize(Permission\TypeOfWork\TypeOfWorkReadPermission::KEY);

        return TypeOfWorkShortListResource::collection(
            $this->repo->getAll(
                filters: $request->validated(),
                limit: $request->validated('limit') ?? TypeOfWorkShortListRequest::DEFAULT_LIMIT
            )
        );
    }
}
