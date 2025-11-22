<?php

namespace App\Http\Controllers\Api\V1\Suppliers;

use App\Exceptions\HasRelatedEntitiesException;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Suppliers\SupplierFilterRequest;
use App\Http\Requests\Suppliers\SupplierRequest;
use App\Http\Requests\Suppliers\SupplierShortListRequest;
use App\Http\Resources\Suppliers\SupplierFullResource;
use App\Http\Resources\Suppliers\SupplierResource;
use App\Http\Resources\Suppliers\SupplierShortListResource;
use App\Models\Suppliers\Supplier;
use App\Foundations\Modules\Permission\Permissions as Permission;
use App\Repositories\Suppliers\SupplierRepository;
use App\Services\Suppliers\SupplierService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Response;

class SupplierCrudController extends ApiController
{
    public function __construct(
        protected SupplierRepository $repo,
        protected SupplierService $service,
    )
    {}

    /**
     * @OA\Get(
     *     path="/api/v1/suppliers",
     *     tags={"Suppliers"},
     *     security={{"Basic": {}}},
     *     summary="Get suppliers paginated list",
     *     operationId="GetSuppliersPaginatedList",
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
     *         description="Scope for filter by name, email, phone",
     *         @OA\Schema(type="string", default="null",)
     *     ),
     *
     *     @OA\Response(response=200, description="Paginated data",
     *         @OA\JsonContent(ref="#/components/schemas/SupplierPaginationResource")
     *     ),
     *
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function index(SupplierFilterRequest $request): ResourceCollection
    {
        $this->authorize(Permission\Supplier\SupplierReadPermission::KEY);

        $models = $this->repo->getAllPagination(
            filters:  $request->validated()
        );

        return SupplierResource::collection($models);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/suppliers/shortlist",
     *     tags={"Suppliers"},
     *     security={{"Basic": {}}},
     *     summary="Get Suppliers short list",
     *     operationId="GetSuppliersShortlist",
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
     *     @OA\Response(response=200, description="Supplier data",
     *         @OA\JsonContent(ref="#/components/schemas/SupplierShortListResource"),
     *     ),
     *
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="422", description="Not Found", @OA\JsonContent(ref="#/components/schemas/ValidationErrors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function shortlist(SupplierShortListRequest $request): AnonymousResourceCollection
    {
        $this->authorize(Permission\Supplier\SupplierReadPermission::KEY);

        return SupplierShortListResource::collection(
            $this->repo->getAll(
                filters: $request->validated(),
                limit: $request->validated('limit') ?? SupplierShortListRequest::DEFAULT_LIMIT
            )
        );
    }

    /**
     * @OA\Post(
     *     path="/api/v1/suppliers",
     *     tags={"Suppliers"},
     *     security={{"Basic": {}}},
     *     summary="Create supplier",
     *     operationId="CreateSuppliers",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(ref="#/components/parameters/ValidateOnly"),
     *
     *     @OA\RequestBody(required=true,
     *         @OA\JsonContent(ref="#/components/schemas/SupplierRequest")
     *     ),
     *
     *     @OA\Response(response=201, description="Supplier data",
     *         @OA\JsonContent(ref="#/components/schemas/SupplierFullResource")
     *     ),
     *
     *     @OA\Response(response="422", description="Validation", @OA\JsonContent(ref="#/components/schemas/ValidationErrors")),
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function store(SupplierRequest $request): SupplierFullResource
    {
        $this->authorize(Permission\Supplier\SupplierCreatePermission::KEY);

        $model = $this->service->create($request->getDto());

        return SupplierFullResource::make($model);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/suppliers/{id}",
     *     tags={"Suppliers"},
     *     security={{"Basic": {}}},
     *     summary="Update supplier",
     *     operationId="UpadteSuppliers",
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
     *         @OA\JsonContent(ref="#/components/schemas/SupplierRequest")
     *     ),
     *
     *     @OA\Response(response=201, description="Supplier data",
     *         @OA\JsonContent(ref="#/components/schemas/SupplierFullResource")
     *     ),
     *
     *     @OA\Response(response="422", description="Validation", @OA\JsonContent(ref="#/components/schemas/ValidationErrors")),
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="404", description="Not Found", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function update(SupplierRequest $request, $id): SupplierFullResource
    {
        $this->authorize(Permission\Supplier\SupplierUpdatePermission::KEY);

        /** @var $model Supplier */
        $model = $this->repo->getBy(['id' => $id],['contacts'],
            withException: true,
            exceptionMessage: __("exceptions.supplier.not_found")
        );

        return SupplierFullResource::make(
            $this->service->update($model, $request->getDto())
        );
    }

    /**
     * @OA\Get(
     *     path="/api/v1/suppliers/{id}",
     *     tags={"Suppliers"},
     *     security={{"Basic": {}}},
     *     summary="Get info about supplier",
     *     operationId="GetInfoAboutSuppliers",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *
     *     @OA\Parameter(ref="#/components/parameters/IDPath"),
     *
     *     @OA\Response(response=200, description="Supplier data",
     *         @OA\JsonContent(ref="#/components/schemas/SupplierFullResource")
     *     ),
     *
     *      @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *      @OA\Response(response="404", description="Not Found", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *      @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function show($id): SupplierFullResource
    {
        $this->authorize(Permission\Supplier\SupplierReadPermission::KEY);

        return SupplierFullResource::make(
            $this->repo->getBy(['id' => $id], withException: true,
                exceptionMessage: __("exceptions.supplier.not_found")
            )
        );
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/suppliers/{id}",
     *     tags={"Suppliers"},
     *     security={{"Basic": {}}},
     *     summary="Delete supplier",
     *     operationId="DeleteSupplier",
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
        $this->authorize(Permission\Supplier\SupplierDeletePermission::KEY);

        /** @var $model Supplier */
        $model = $this->repo->getBy(['id' => $id],
            withException: true,
            exceptionMessage: __("exceptions.supplier.not_found")
        );

        try {
            $this->service->delete($model);

            return $this->successJsonMessage(null, Response::HTTP_NO_CONTENT);
        } catch (HasRelatedEntitiesException $e) {
            $link = str_replace('{id}', $model->id, config('routes.front.inventories_with_supplier_filter_url'));

            return $this->errorJsonMessage(__('exceptions.supplier.has_inventory', ['link' => $link]),
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }
    }
}
