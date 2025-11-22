<?php

namespace App\Http\Controllers\Api\V1\Inventories\Inventory;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Inventories\Inventory\InventoryFilterRequest;
use App\Http\Requests\Inventories\Inventory\InventoryRequest;
use App\Http\Requests\Inventories\Inventory\InventoryShortListRequest;
use App\Foundations\Modules\Permission\Permissions as Permission;
use App\Http\Resources\Inventories\Inventory\InventoryListResource;
use App\Http\Resources\Inventories\Inventory\InventoryPaginationResource;
use App\Http\Resources\Inventories\Inventory\InventoryResource;
use App\Http\Resources\Inventories\Inventory\InventoryShortListPaginateResource;
use App\Http\Resources\Inventories\Inventory\InventoryShortListResource;
use App\Models\Inventories\Inventory;
use App\Repositories\Inventories\InventoryRepository;
use App\Services\Inventories\InventoryService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Response;

class CrudController extends ApiController
{
    public function __construct(
        protected InventoryRepository $repo,
        protected InventoryService $service,
    )
    {}

    /**
     * @OA\Get(
     *     path="/api/v1/inventories",
     *     tags={"Inventory"},
     *     security={{"Basic": {}}},
     *     summary="Get inventory pagination list",
     *     operationId="GetInventoryPaginationList",
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
     *         description="Scope for filter by name and stock_number",
     *         @OA\Schema(type="string", default="null",)
     *     ),
     *     @OA\Parameter(name="category_id", in="query", description="Category id", required=false,
     *         @OA\Schema(type="integer", default="1")
     *     ),
     *     @OA\Parameter(name="brand_id", in="query", description="Brand id", required=false,
     *         @OA\Schema(type="integer", default="1")
     *     ),
     *     @OA\Parameter(name="supplier_id", in="query", description="Supplier id", required=false,
     *         @OA\Schema(type="integer", default="1")
     *     ),
     *     @OA\Parameter(name="status", in="query", description="Status", required=false,
     *         @OA\Schema(type="string", example="in_stock", enum={"in_stock", "out_of_stock"})
     *     ),
     *     @OA\Parameter(name="only_min_limit", in="query", description="Resc only min limit", required=false,
     *         @OA\Schema(type="bool", default="true")
     *     ),
     *     @OA\Parameter(name="for_shop", in="query", description="For shop", required=false,
     *         @OA\Schema(type="bool", default="true")
     *     ),
     *
     *     @OA\Response(response=200, description="Inventory data",
     *         @OA\JsonContent(ref="#/components/schemas/InventoryPaginationResource")
     *     ),
     *
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function index(InventoryFilterRequest $request): ResourceCollection
    {
        $this->authorize(Permission\Inventory\Inventory\InventoryReadPermission::KEY);

        return InventoryPaginationResource::collection(
            $this->repo->getCustomPagination(
                filters: $request->validated(),
                relations: ['brand', 'unit', 'category', 'supplier']
            )
        );
    }

    /**
     * @OA\Get(
     *     path="/api/v1/inventories/shortlist",
     *     tags={"Inventory"},
     *     security={{"Basic": {}}},
     *     summary="Get Inventory short list",
     *     operationId="GetInventoryShortlist",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *
     *     @OA\Parameter(ref="#/components/parameters/ID"),
     *     @OA\Parameter(ref="#/components/parameters/Limit"),
     *
     *     @OA\Parameter(name="search", in="query", description="Scope for filter by name and stock_number", required=false,
     *          @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(name="without_ids", in="query", description="list of products without these products", required=false,
     *           @OA\Schema(type="array", example={1, 22, 3}, @OA\Items(type="integer"))
     *     ),
     *
     *     @OA\Response(response=200, description="Inventory data",
     *         @OA\JsonContent(ref="#/components/schemas/InventoryShortListResource"),
     *     ),
     *
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="422", description="Not Found", @OA\JsonContent(ref="#/components/schemas/ValidationErrors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function shortlist(InventoryShortListRequest $request): AnonymousResourceCollection
    {
        $this->authorize(Permission\Inventory\Inventory\InventoryReadPermission::KEY);

        return InventoryShortListResource::collection(
            $this->repo->getAll(
                relation: ['unit'],
                filters: $request->validated(),
                limit: $request->validated('limit') ?? InventoryShortListRequest::DEFAULT_LIMIT,
            )
        );
    }

    /**
     * @OA\Get(
     *     path="/api/v1/inventories/list",
     *     tags={"Inventory"},
     *     security={{"Basic": {}}},
     *     summary="Get Inventory list",
     *     operationId="GetInventorylist",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *
     *     @OA\Parameter(ref="#/components/parameters/ID"),
     *     @OA\Parameter(ref="#/components/parameters/Limit"),
     *
     *     @OA\Parameter(name="search", in="query", description="Scope for filter by name and stock_number", required=false,
     *          @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(name="without_ids", in="query", description="list of products without these products", required=false,
     *           @OA\Schema(type="array", example={1, 22, 3}, @OA\Items(type="integer"))
     *     ),
     *
     *     @OA\Response(response=200, description="Inventory data",
     *         @OA\JsonContent(ref="#/components/schemas/InventoryShortListResource"),
     *     ),
     *
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="422", description="Not Found", @OA\JsonContent(ref="#/components/schemas/ValidationErrors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function list(InventoryShortListRequest $request): AnonymousResourceCollection
    {
        $this->authorize(Permission\Inventory\Inventory\InventoryReadPermission::KEY);

        return InventoryListResource::collection(
            $this->repo->getAll(
                relation: ['unit', 'media', 'brand'],
                filters: $request->validated(),
                limit: $request->validated('limit') ?? InventoryShortListRequest::DEFAULT_LIMIT,
            )
        );
    }

    /**
     * @OA\Get(
     *     path="/api/v1/inventories/shortlist-paginate",
     *     tags={"Inventory"},
     *     security={{"Basic": {}}},
     *     summary="Get Inventory short list paginate",
     *     operationId="GetInventoryShortListPaginate",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *
     *     @OA\Parameter(ref="#/components/parameters/Page"),
     *     @OA\Parameter(ref="#/components/parameters/PerPage"),
     *
     *     @OA\Parameter(ref="#/components/parameters/ID"),
     *     @OA\Parameter(ref="#/components/parameters/Limit"),
     *
     *     @OA\Parameter(name="search", in="query", description="Scope for filter by name and stock_number", required=false,
     *          @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(name="without_ids", in="query", description="list of products without these products", required=false,
     *           @OA\Schema(type="array", example={1, 22, 3}, @OA\Items(type="integer"))
     *     ),
     *
     *     @OA\Response(response=200, description="Inventory data",
     *          @OA\JsonContent(ref="#/components/schemas/InventoryShortListPaginateResource")
     *      ),
     *
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="422", description="Not Found", @OA\JsonContent(ref="#/components/schemas/ValidationErrors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     * @throws AuthorizationException
     */
    public function shortlistPaginate(InventoryFilterRequest $request): AnonymousResourceCollection
    {
        $this->authorize(Permission\Inventory\Inventory\InventoryReadPermission::KEY);

        return InventoryShortListPaginateResource::collection(
            $this->repo->getCustomPagination(
                relations: ['brand', 'unit', 'category', 'supplier'],
                filters: $request->validated()
            )
        );
    }

    /**
     * @OA\Post(
     *     path="/api/v1/inventories",
     *     tags={"Inventory"},
     *     security={{"Basic": {}}},
     *     summary="Create inventory",
     *     operationId="CreateInventory",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(ref="#/components/parameters/ValidateOnly"),
     *
     *     @OA\RequestBody(required=true,
     *         @OA\JsonContent(ref="#/components/schemas/InventoryRequest")
     *     ),
     *
     *     @OA\Response(response=201, description="Inventory data",
     *         @OA\JsonContent(ref="#/components/schemas/InventoryResource")
     *     ),
     *
     *     @OA\Response(response="422", description="Validation", @OA\JsonContent(ref="#/components/schemas/ValidationErrors")),
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function store(InventoryRequest $request): InventoryResource|JsonResponse
    {
        $this->authorize(Permission\Inventory\Inventory\InventoryCreatePermission::KEY);

        return InventoryResource::make(
            $this->service->create($request->getDto())
        );
    }

    /**
     * @OA\Post(
     *     path="/api/v1/inventories/{id}",
     *     tags={"Inventory"},
     *     security={{"Basic": {}}},
     *     summary="Update inventory",
     *     operationId="UpadteInventory",
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
     *         @OA\JsonContent(ref="#/components/schemas/InventoryRequest")
     *     ),
     *
     *     @OA\Response(response=201, description="Inventory data",
     *         @OA\JsonContent(ref="#/components/schemas/InventoryResource")
     *     ),
     *
     *     @OA\Response(response="422", description="Validation", @OA\JsonContent(ref="#/components/schemas/ValidationErrors")),
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="404", description="Not Found", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function update(InventoryRequest $request, $id): InventoryResource|JsonResponse
    {
        $this->authorize(Permission\Inventory\Inventory\InventoryUpdatePermission::KEY);

        /** @var $model Inventory */
        $model = $this->repo->getBy(['id' => $id],
            withException: true,
            exceptionMessage: __("exceptions.inventories.inventory.not_found")
        );

        $model = $this->service->update($model, $request->getDto());

        $model->refresh();

        return InventoryResource::make($model);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/inventories/{id}",
     *     tags={"Inventory"},
     *     security={{"Basic": {}}},
     *     summary="Get info about inventory",
     *     operationId="GetInfoAboutInventory",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *
     *     @OA\Parameter(ref="#/components/parameters/IDPath"),
     *
     *     @OA\Response(response=200, description="Inventory data",
     *         @OA\JsonContent(ref="#/components/schemas/InventoryResource")
     *     ),
     *
     *      @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *      @OA\Response(response="404", description="Not Found", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *      @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function show($id): InventoryResource
    {
        $this->authorize(Permission\Inventory\Inventory\InventoryReadPermission::KEY);

        return InventoryResource::make(
            $this->repo->getBy(['id' => $id],
                relations: ['category', 'brand', 'supplier', 'unit'],
                withException: true,
                exceptionMessage: __("exceptions.inventories.inventory.not_found")
            )
        );
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/inventoryies/{id}",
     *     tags={"Inventory"},
     *     security={{"Basic": {}}},
     *     summary="Delete inventory",
     *     operationId="DeleteInventory",
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
        $this->authorize(Permission\Inventory\Inventory\InventoryDeletePermission::KEY);

        /** @var $model Inventory */
        $model = $this->repo->getBy(['id' => $id],
            withException: true,
            exceptionMessage: __("exceptions.inventories.inventory.not_found")
        );

        if ($model->isInStock() || $model->hasRelatedEntities()) {
            return $this->errorJsonMessage($this->getMessageForDeleteFailed($model),
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $this->service->delete($model);

        return $this->successJsonMessage(null, Response::HTTP_NO_CONTENT);
    }

    protected function getMessageForDeleteFailed(Inventory $model): string
    {
        if($model->isInStock()){
            return __("exceptions.inventories.inventory.cant_deleted_in_stock");
        }

        $message = trans('This part is used in ') . '%s.';
        $usedIn = [];
        if ($model->hasRelatedOpenOrders()) {
            $url = str_replace('{id}', $model->id, config('routes.front.bs_open_orders_with_inventory_filter_url'));
            $usedIn[] = '<a href="' . $url . '">' . trans('open orders') . '</a>';
        }

        if ($model->hasRelatedDeletedOrders()) {
            $url = str_replace('{id}', $model->id, config('routes.front.bs_deleted_orders_with_inventory_filter_url'));
            $usedIn[] = '<a href="' . $url . '">' . trans('deleted orders') . '</a>';
        }
        if ($usedIn) {
            $message .= trans(' Please delete order permanently first.');
        }

        if ($model->hasRelatedTypesOfWork()) {
            $url = str_replace('{id}', $model->id, config('routes.front.bs_types_of_work_with_inventory_filter_url'));
            $usedIn[] = '<a href="' . $url . '">types of work</a>';
        }

        return sprintf($message, implode(trans(' and '), $usedIn));
    }
}
