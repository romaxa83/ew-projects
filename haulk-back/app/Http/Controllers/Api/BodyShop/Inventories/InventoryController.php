<?php

namespace App\Http\Controllers\Api\BodyShop\Inventories;

use App\Exceptions\HasRelatedEntitiesException;
use App\Exports\BodyShop\InventoryExport;
use App\Http\Controllers\ApiController;
use App\Http\Requests\BodyShop\Inventories\InventoryHistoryRequest;
use App\Http\Requests\BodyShop\Inventories\InventoryIndexRequest;
use App\Http\Requests\BodyShop\Inventories\InventoryRequest;
use App\Http\Requests\BodyShop\Inventories\InventoryTransactionsReportRequest;
use App\Http\Requests\BodyShop\Inventories\PurchaseRequest;
use App\Http\Requests\BodyShop\Inventories\SoldRequest;
use App\Http\Requests\BodyShop\PaginationRequest;
use App\Http\Requests\SearchRequest;
use App\Http\Resources\BodyShop\Inventories\InventoryTransactionsReportPaginateResource;
use App\Http\Resources\BodyShop\Inventories\InventoryTransactionsReportTotalResource;
use App\Http\Resources\BodyShop\Inventories\TransactionPaginateResource;
use App\Http\Resources\BodyShop\Inventories\TransactionResource;
use App\Http\Resources\BodyShop\Inventories\InventoryPaginateResource;
use App\Http\Resources\BodyShop\Inventories\InventoryResource;
use App\Http\Resources\BodyShop\Inventories\InventoryShortListResource;
use App\Http\Resources\BodyShop\History\HistoryListResource;
use App\Http\Resources\BodyShop\History\HistoryPaginatedResource;
use App\Models\BodyShop\Inventories\Inventory;
use App\Models\BodyShop\Inventories\Transaction;
use App\Services\BodyShop\Inventories\InventoryService;
use App\Services\BodyShop\Inventories\InvoiceService;
use Carbon\CarbonImmutable;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class InventoryController extends ApiController
{
    protected InventoryService $service;

    public function __construct(InventoryService $service)
    {
        parent::__construct();

        $this->service = $service;
        $this->service->setUser(authUser());
    }

    /**
     * @param InventoryIndexRequest $request
     * @return AnonymousResourceCollection
     *
     * @OA\Get(
     *     path="/api/body-shop/inventories",
     *     tags={"Inventory Body Shop"},
     *     summary="Get inventory list",
     *     operationId="Get Inventory data",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="q", in="query", description="Scope for filter by name, stock number", required=false,
     *          @OA\Schema( type="string", default="name",)
     *     ),
     *     @OA\Parameter(name="page", in="query", description="Page number", required=false,
     *          @OA\Schema( type="integer", default="5")
     *     ),
     *     @OA\Parameter(name="per_page", in="query", description="States per page", required=false,
     *          @OA\Schema(type="integer", default="10")
     *     ),
     *     @OA\Parameter(name="category_id", in="query", description="Category id", required=false,
     *          @OA\Schema(type="integer", default="1")
     *     ),
     *     @OA\Parameter(name="supplier_id", in="query", description="Supplier id", required=false,
     *          @OA\Schema(type="integer", default="1")
     *     ),
     *     @OA\Parameter(name="status", in="query", description="Status", required=false,
     *          @OA\Schema(type="string", default="1", enum={"in_stock", "out_of_stock"})
     *     ),
     *     @OA\Parameter(name="only_min_limit", in="query", description="Resc only min limit", required=false,
     *          @OA\Schema(type="bool", default="true")
     *     ),
     *     @OA\Parameter(name="for_sale", in="query", description="For sale", required=false,
     *          @OA\Schema(type="bool", default="true")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/InventoryPaginate"),
     *     )
     * )
     *
     * @todo moved
     */
    public function index(InventoryIndexRequest $request): AnonymousResourceCollection
    {
        $this->authorize('inventories');

        $inventories = Inventory::query()
            ->filter($request->validated())
            ->orderByRaw('CASE WHEN quantity = 0 THEN 0 ELSE 1 END desc, name asc')
            ->paginate($request->per_page);

        return InventoryPaginateResource::collection($inventories);
    }

    /**
     * @param InventoryRequest $request
     * @return InventoryResource|JsonResponse
     * @throws AuthorizationException
     * @throws Throwable
     * @OA\Post(path="/api/body-shop/inventories",
     *     tags={"Inventory Body Shop"},
     *     summary="Create Inventory",
     *     operationId="Create Inventory",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/ValidateOnly"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="name", in="query", description="Inventory name", required=true,
     *          @OA\Schema(type="string", default="Inventory 1",)
     *     ),
     *     @OA\Parameter(name="stock_number", in="query", description="Inventory stock number", required=true,
     *          @OA\Schema(type="string", default="123GFDJF",)
     *     ),
     *     @OA\Parameter(name="category_id", in="query", description="Inventory category id", required=true,
     *          @OA\Schema(type="integer", default="1",)
     *     ),
     *     @OA\Parameter(name="price_retail", in="query", description="Inventory retail price", required=false,
     *          @OA\Schema(type="number", default="1.00",)
     *     ),
     *     @OA\Parameter(name="min_limit", in="query", description="Inventory min limit", required=false,
     *          @OA\Schema(type="number", default="1",)
     *     ),
     *     @OA\Parameter(name="supplier_id", in="query", description="Inventory supplier id", required=false,
     *          @OA\Schema(type="integer", default="1",)
     *     ),
     *     @OA\Parameter(name="unit_id", in="query", description="Inventory unit id", required=false,
     *          @OA\Schema(type="integer", default="1",)
     *     ),
     *     @OA\Parameter(name="for_sale", in="query", description="For sale", required=false,
     *          @OA\Schema(type="boolean", default="false",)
     *     ),
     *     @OA\Parameter(name="length", in="query", description="Length", required=false,
     *          @OA\Schema(type="number", default="1.00",)
     *     ),
     *     @OA\Parameter(name="width", in="query", description="Width", required=false,
     *          @OA\Schema(type="number", default="1.00",)
     *     ),
     *     @OA\Parameter(name="height", in="query", description="Height", required=false,
     *          @OA\Schema(type="number", default="1.00",)
     *     ),
     *     @OA\Parameter(name="weight", in="query", description="Weight", required=false,
     *          @OA\Schema(type="number", default="1.00",)
     *     ),
     *     @OA\Parameter(name="min_limit_price", in="query", description="Min limit price", required=false,
     *           @OA\Schema(type="number", default="1.00",)
     *      ),
     *     @OA\Parameter(name="notes", in="query", description="Inventory additiona notes", required=false,
     *          @OA\Schema(type="string", default="notes",)
     *     ),
     *     @OA\Parameter(name="purchase", in="query", description="Purchase data", required=true,
     *          @OA\Schema(type="object",
     *              allOf={
     *                  @OA\Schema(
     *                      @OA\Property(property="quantity", type="number", description="Quantity"),
     *                      @OA\Property(property="cost", type="number", description="Cost"),
     *                      @OA\Property(property="invoice_number", type="string", description="Invoice number"),
     *                      @OA\Property(property="date", type="string", description="Date, format m/d/Y"),
     *                 )
     *              }
     *          )
     *     ),
     *     @OA\Response(response=201, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/Inventory")
     *     ),
     * )
     * @todo moved
     */
    public function store(InventoryRequest $request)
    {
        $this->authorize('inventories create');

        try {
            $inventory = $this->service->create($request->getDto());

            return InventoryResource::make($inventory);
        } catch (Exception $exception) {
            Log::error($exception);

            return $this->makeErrorResponse($exception->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     *
     * @OA\Get(
     *     path="/api/body-shop/inventories/{inventoryId}",
     *     tags={"Inventory Body Shop"},
     *     summary="Get inventory record",
     *     operationId="Get inventory record",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="Inventory id",
     *          required=true,
     *          @OA\Schema(
     *              type="integer",
     *          )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/Inventory")
     *     ),
     * )
     * @param Inventory $category
     * @return InventoryResource
     * @throws AuthorizationException
     * @todo moved
     */
    public function show(Inventory $inventory): InventoryResource
    {
        $this->authorize('inventories read');

        return InventoryResource::make($inventory);
    }

    /**
     * Update the specified resource in storage.
     *
     * @OA\Put(
     *     path="/api/body-shop/inventories/{inventoryId}",
     *     tags={"Inventory Body Shop"},
     *     summary="Update inventory record",
     *     operationId="Update invettory",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="id", in="path", description="Inventory id", required=true,
     *          @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(name="name", in="query", description="Inventory name", required=true,
     *          @OA\Schema(type="string", default="Inventory 1",)
     *     ),
     *     @OA\Parameter(name="stock_number", in="query", description="Inventory stock number", required=true,
     *          @OA\Schema(type="string", default="123GFDJF",)
     *     ),
     *     @OA\Parameter(name="category_id", in="query", description="Inventory category id", required=true,
     *          @OA\Schema(type="integer", default="1",)
     *     ),
     *     @OA\Parameter(name="price_retail", in="query", description="Inventory retail price", required=false,
     *          @OA\Schema(type="number", default="1.00",)
     *     ),
     *     @OA\Parameter(name="min_limit", in="query", description="Inventory min limin", required=false,
     *          @OA\Schema(type="number", default="1",)
     *     ),
     *     @OA\Parameter(name="supplier_id", in="query", description="Inventory supplier id", required=false,
     *          @OA\Schema(type="integer", default="1",)
     *     ),
     *     @OA\Parameter(name="unit_id", in="query", description="Inventory unit id", required=false,
     *          @OA\Schema(type="integer", default="1",)
     *     ),
     *     @OA\Parameter(name="notes", in="query", description="Inventory additiona notes", required=false,
     *          @OA\Schema(type="string", default="notes",)
     *     ),
     *     @OA\Parameter(name="for_sale", in="query", description="For sale", required=false,
     *          @OA\Schema(type="boolean", default="false",)
     *     ),
     *     @OA\Parameter(name="length", in="query", description="Length", required=false,
     *          @OA\Schema(type="number", default="1.00",)
     *     ),
     *     @OA\Parameter(name="width", in="query", description="Width", required=false,
     *          @OA\Schema(type="number", default="1.00",)
     *     ),
     *     @OA\Parameter(name="height", in="query", description="Height", required=false,
     *          @OA\Schema(type="number", default="1.00",)
     *     ),
     *     @OA\Parameter(name="weight", in="query", description="Weight", required=false,
     *          @OA\Schema(type="number", default="1.00",)
     *     ),
     *     @OA\Parameter(name="min_limit_price", in="query", description="Min limit price", required=false,
     *          @OA\Schema(type="number", default="1.00",)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/Inventory")
     *     ),
     * )
     * @param InventoryRequest $request
     * @param Inventory $inventory
     * @return InventoryResource|JsonResponse
     * @throws AuthorizationException
     * @throws Throwable
     * @todo moved
     */
    public function update(InventoryRequest $request, Inventory $inventory)
    {
        $this->authorize('inventories update');

        try {
            $this->service->update($inventory, $request->getDto());

            return InventoryResource::make($inventory);
        } catch (Exception $e) {
            Log::error($e);

            return $this->makeErrorResponse(null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get inventory history
     *
     * @param int $inventoryId
     * @return AnonymousResourceCollection|JsonResponse
     *
     * @throws AuthorizationException
     * @OA\Get(
     *     path="/api/body-shop/inventories/{inventoryId}/history",
     *     tags={"Inventory Body Shop"},
     *     summary="Get inventoyr history",
     *     operationId="Get inventory history",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/HistoryListResourceBS")
     *     ),
     * )
     * @todo moved
     */
    public function history(int $inventoryId)
    {
        $this->authorize('inventories read');

        try {
            if (!Inventory::find($inventoryId)) {
                return $this->makeErrorResponse(null, Response::HTTP_NOT_FOUND);
            }

            return HistoryListResource::collection(
                $this->service->getHistory($inventoryId)
            );
        } catch (Exception $e) {
            Log::error($e->getTraceAsString());
            return $this->makeErrorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get inventory history detailed paginate
     *
     * @param int $inventoryId
     * @param InventoryHistoryRequest $request
     * @return AnonymousResourceCollection|JsonResponse
     *
     * @throws AuthorizationException
     * @OA\Get(
     *     path="/api/body-shop/inventories/{inventoryId}/history-detailed",
     *     tags={"Inventory Body Shop"},
     *     summary="Get inventory history detailed",
     *     operationId="Get inventory history detailed",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(
     *          name="dates_range",
     *          in="query",
     *          description="06/06/2021 - 06/14/2021",
     *          required=false,
     *          @OA\Schema(
     *              type="string",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="user_id",
     *          in="query",
     *          description="user_id",
     *          required=false,
     *          @OA\Schema(
     *              type="integer"
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="per_page",
     *          in="query",
     *          description="per page",
     *          required=false,
     *          @OA\Schema(
     *              type="integer",
     *              default="10"
     *          )
     *     ),
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/HistoryPaginatedResourceBS")
     *     ),
     * )
     * @todo moved
     */
    public function historyDetailed(int $inventoryId, InventoryHistoryRequest $request)
    {
        $this->authorize('inventories read');

        try {
            if (!Inventory::find($inventoryId)) {
                return $this->makeErrorResponse(null, Response::HTTP_NOT_FOUND);
            }

            return HistoryPaginatedResource::collection(
                $this->service->getHistoryDetailed($inventoryId, $request->validated(), $request->per_page)
            );
        } catch (Exception $e) {
            Log::error($e->getTraceAsString());
            return $this->makeErrorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param SearchRequest $request
     * @return AnonymousResourceCollection
     *
     * @OA\Get(
     *     path="/api/body-shop/inventories/shortlist",
     *     tags={"Inventory Body Shop"},
     *     summary="Get inventory short list",
     *     operationId="Get Inventory data",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="q", in="query", description="Scope for filter by name, stock number", required=false,
     *          @OA\Schema( type="string", default="name",)
     *     ),
     *     @OA\Parameter(name="searchid", in="query", description="Filter by id", required=false,
     *          @OA\Schema( type="integer", default="1",)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/InventoryShortList"),
     *     )
     * )
     * @todo moved
     */
    public function shortlist(SearchRequest $request): AnonymousResourceCollection
    {
        $inventories = Inventory::query()
            ->filter($request->validated())
            ->limit(SearchRequest::DEFAULT_LIMIT)
            ->get();

        return InventoryShortListResource::collection($inventories);
    }

    /**
     * @param PurchaseRequest $request
     * @param Inventory $inventory
     * @return TransactionResource|JsonResponse
     * @throws AuthorizationException
     * @throws Throwable
     * @OA\Post(path="/api/body-shop/inventories/{inventoryId}/purchase",
     *     tags={"Inventory Body Shop"},
     *     summary="Purchase Inventory",
     *     operationId="Purchase Inventory",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/ValidateOnly"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="quantity", in="query", description="quantity", required=true,
     *          @OA\Schema(type="number", default="1",)
     *     ),
     *     @OA\Parameter(name="cost", in="query", description="Cost", required=true,
     *          @OA\Schema(type="number", default="100",)
     *     ),
     *     @OA\Parameter(name="invoice_number", in="query", description="Invoice number", required=false,
     *          @OA\Schema(type="string", default="1",)
     *     ),
     *     @OA\Parameter(name="date", in="query", description="Date, format m/d/Y", required=true,
     *          @OA\Schema(type="strng", default="10/25/2023",)
     *     ),
     *     @OA\Response(response=201, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/InventoryTransaction")
     *     ),
     * )
     * @todo moved
     */
    public function purchase(Inventory $inventory, PurchaseRequest $request)
    {
        $this->authorize('inventories update');

        try {
            $account = $this->service->account($inventory, $request->getDto());

            return TransactionResource::make($account);
        } catch (Exception $exception) {
            Log::error($exception);

            return $this->makeErrorResponse($exception->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param SoldRequest $request
     * @param Inventory $inventory
     * @return TransactionResource|JsonResponse
     * @throws AuthorizationException
     * @throws Throwable
     * @OA\Post(path="/api/body-shop/inventories/{inventoryId}/sold",
     *     tags={"Inventory Body Shop"},
     *     summary="Sold Inventory",
     *     operationId="Purchase Inventory",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/ValidateOnly"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="quantity", in="query", description="quantity", required=true,
     *          @OA\Schema(type="number", default="1",)
     *     ),
     *     @OA\Parameter(name="price", in="query", description="Price", required=true,
     *          @OA\Schema(type="number", default="100",)
     *     ),
     *     @OA\Parameter(name="invoice_number", in="query", description="Invoice number", required=false,
     *          @OA\Schema(type="string", default="1",)
     *     ),
     *     @OA\Parameter(name="date", in="query", description="Date, format m/d/Y", required=true,
     *          @OA\Schema(type="string", default="10/25/2023",)
     *     ),
     *     @OA\Parameter(name="describe", in="query", description="Describe", required=true,
     *          @OA\Schema(type="string", enum={"sold","broke","defect"})
     *     ),
     *     @OA\Parameter(name="payment_date", in="query", description="Due Date, format m/d/Y", required=false,
     *          @OA\Schema(type="string", default="10/25/2023",)
     *     ),
     *     @OA\Parameter(name="payment_method", in="query", description="Payment method", required=false,
     *          @OA\Schema(type="string", default="cash",enum={"cash", "check", "money_order", "quick_pay", "paypal", "cashapp", "venmo", "zelle", "credit_card", "card", "wire_transfer"})
     *     ),
     *     @OA\Parameter(name="tax", in="query", description="Tax", required=false,
     *          @OA\Schema(type="number", default="10",)
     *     ),
     *     @OA\Parameter(name="discount", in="query", description="Discount", required=false,
     *          @OA\Schema(type="number", default="10",)
     *     ),
     *     @OA\Parameter(name="first_name", in="query", description="First Name", required=false,
     *          @OA\Schema(type="string", default="First",)
     *     ),
     *     @OA\Parameter(name="last_name", in="query", description="Last Name", required=false,
     *          @OA\Schema(type="string", default="Last",)
     *     ),
     *     @OA\Parameter(name="company_name", in="query", description="Company Name", required=false,
     *          @OA\Schema(type="string", default="Company",)
     *     ),
     *     @OA\Parameter(name="phone", in="query", description="Phone", required=false,
     *          @OA\Schema(type="string", default="",)
     *     ),
     *     @OA\Parameter(name="email", in="query", description="Email", required=false,
     *          @OA\Schema(type="string", default="",)
     *     ),
     *     @OA\Response(response=201, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/InventoryTransaction")
     *     ),
     * )
     * @todo moved
     */
    public function sold(Inventory $inventory, SoldRequest $request)
    {
        $this->authorize('inventories update');

        try {
            $account = $this->service->account($inventory, $request->getDto());

            return TransactionResource::make($account);
        } catch (Exception $exception) {
            Log::error($exception);

            return $this->makeErrorResponse($exception->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param PaginationRequest $request
     * @return AnonymousResourceCollection
     *
     * @OA\Get(
     *     path="/api/body-shop/inventories/{inventoryId}/transactions",
     *     tags={"Inventory Body Shop"},
     *     summary="Get inventory Transactions list",
     *     operationId="Get Inventory Transactions data",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="id", in="path", description="Inventory id", required=true,
     *          @OA\Schema(type="integer",)
     *     ),
     *     @OA\Parameter(name="page", in="query", description="Page number", required=false,
     *          @OA\Schema( type="integer", default="5")
     *     ),
     *     @OA\Parameter(name="per_page", in="query", description="States per page", required=false,
     *          @OA\Schema(type="integer", default="10")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/InventoryTransactionPaginate"),
     *     )
     * )
     * @todo moved
     */
    public function transactions(Inventory $inventory, PaginationRequest $request): AnonymousResourceCollection
    {
        $this->authorize('inventories read');

        $transactions = $inventory->transactions()
            ->select('*')
            ->selectPriceWithTaxAndDiscount()
            ->where('is_reserve', false)
            ->orderBy('id', 'desc')
            ->paginate($request->per_page);

        return TransactionPaginateResource::collection($transactions);
    }

    /**
     * @param PaginationRequest $request
     * @return AnonymousResourceCollection
     *
     * @OA\Get(
     *     path="/api/body-shop/inventories/{inventoryId}/reserve",
     *     tags={"Inventory Body Shop"},
     *     summary="Get inventory Transactions list",
     *     operationId="Get Inventory Transactions data",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="id", in="path", description="Inventory id", required=true,
     *          @OA\Schema(type="integer",)
     *     ),
     *     @OA\Parameter(name="page", in="query", description="Page number", required=false,
     *          @OA\Schema( type="integer", default="5")
     *     ),
     *     @OA\Parameter(name="per_page", in="query", description="States per page", required=false,
     *          @OA\Schema(type="integer", default="10")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/InventoryTransactionPaginate"),
     *     )
     * )
     * @todo moved
     */
    public function transactionsReserved(Inventory $inventory, PaginationRequest $request): AnonymousResourceCollection
    {
        $this->authorize('inventories read');

        $transactions = $inventory->transactions()
            ->where('is_reserve', true)
            ->where('operation_type', Transaction::OPERATION_TYPE_SOLD)
            ->orderBy('id', 'desc')
            ->paginate($request->per_page);

        return TransactionPaginateResource::collection($transactions);
    }

    /**
     * @param InventoryTransactionsReportRequest $request
     * @return AnonymousResourceCollection
     *
     * @OA\Get(
     *     path="/api/body-shop/inventories/report",
     *     tags={"Inventory Body Shop"},
     *     summary="Get inventory report ",
     *     operationId="Get Inventory report data",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="q", in="query", description="Search string", required=false,
     *          @OA\Schema( type="string",)
     *     ),
     *     @OA\Parameter(name="category_id", in="query", description="Category id", required=false,
     *          @OA\Schema( type="integer", default="5")
     *     ),
     *     @OA\Parameter(name="supplier_id", in="query", description="Supplier id", required=false,
     *          @OA\Schema( type="integer", default="5")
     *     ),
     *     @OA\Parameter(name="date_from", in="query", description="Date from, m/d/Y", required=false,
     *          @OA\Schema( type="string", default="10/20/2013")
     *     ),
     *     @OA\Parameter(name="date_to", in="query", description="Date to, m/d/Y", required=false,
     *          @OA\Schema( type="string", default="10/20/2023")
     *     ),
     *     @OA\Parameter(name="transaction_type", in="query", description="Transaction type", required=false,
     *          @OA\Schema( type="string", enum={"purchase","sold"})
     *     ),
     *     @OA\Parameter(name="page", in="query", description="Page number", required=false,
     *          @OA\Schema( type="integer", default="5")
     *     ),
     *     @OA\Parameter(name="per_page", in="query", description="States per page", required=false,
     *          @OA\Schema(type="integer", default="10")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/InventoryTransactionReportPaginate"),
     *     )
     * )
     * @todo moved
     */
    public function transactionsReport(InventoryTransactionsReportRequest $request): AnonymousResourceCollection
    {
        $this->authorize('reports-bs inventories');

        $transactions = Transaction::query()
            ->select('*')
            ->selectPriceWithTaxAndDiscount()
            ->filter($request->validated())
            ->where('is_reserve', false)
            ->orderByRaw('transaction_date desc, id desc')
            ->paginate($request->per_page);

        return InventoryTransactionsReportPaginateResource::collection($transactions);
    }

    /**
     * @param InventoryTransactionsReportRequest $request
     * @return InventoryTransactionsReportTotalResource
     *
     * @OA\Get(
     *     path="/api/body-shop/inventories/report-total",
     *     tags={"Inventory Body Shop"},
     *     summary="Get inventory report total",
     *     operationId="Get Inventory report total data",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="q", in="query", description="Search string", required=false,
     *          @OA\Schema( type="string",)
     *     ),
     *     @OA\Parameter(name="category_id", in="query", description="Category id", required=false,
     *          @OA\Schema( type="integer", default="5")
     *     ),
     *     @OA\Parameter(name="supplier_id", in="query", description="Supplier id", required=false,
     *          @OA\Schema( type="integer", default="5")
     *     ),
     *     @OA\Parameter(name="date_from", in="query", description="Date from, m/d/Y", required=false,
     *          @OA\Schema( type="string", default="10/20/2013")
     *     ),
     *     @OA\Parameter(name="date_to", in="query", description="Date to, m/d/Y", required=false,
     *          @OA\Schema( type="string", default="10/20/2023")
     *     ),
     *     @OA\Parameter(name="transaction_type", in="query", description="Transaction type", required=false,
     *          @OA\Schema( type="string", enum={"purchase","sold"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/InventoryTransactionsReportTotalBS"),
     *     )
     * )
     * @todo moved
     */
    public function transactionsReportTotal(InventoryTransactionsReportRequest $request): InventoryTransactionsReportTotalResource
    {
        $this->authorize('reports-bs inventories');

        $priceWithDiscountAndTax = 'ROUND(price - (price * coalesce(discount, 0) / 100) + ((price - (price *  coalesce(discount, 0) / 100)) *  coalesce(tax, 0) / 100), 2)';
        $total = Transaction::query()
            ->selectRaw('
                SUM(quantity * CASE WHEN operation_type = \'sold\' THEN ' . $priceWithDiscountAndTax . ' ELSE 0 END) as price_total,
                SUM(quantity * CASE WHEN operation_type = \'purchase\' THEN ' . $priceWithDiscountAndTax . ' ELSE 0 END) as cost_total
            ')
            ->filter($request->validated())
            ->where('is_reserve', false)
            ->first();

        return InventoryTransactionsReportTotalResource::make($total);
    }

    /**
     * Get Invoice pdf file
     *
     * @param Transaction $transaction
     * @param InvoiceService $invoiceService
     * @return JsonResponse|StreamedResponse
     *
     * @OA\Get(
     *     path="/api/body-shop/inventories/transactions/{transactionId}/generate-invoice",
     *     tags={"Inventories Body Shop"},
     *     summary="Get Invoice pdf file",
     *     operationId="Get Invoice pdf file",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="id", in="path", description="Transaction id", required=true,
     *          @OA\Schema(type="integer",)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *     ),
     * )
     * @todo moved
     */
    public function generateInvoice(Transaction $transaction,InvoiceService $invoiceService)
    {
        if ($transaction->describe !== Transaction::DESCRIBE_SOLD && !$transaction->isSold()) {
            return $this->makeErrorResponse(null, Response::HTTP_NOT_FOUND);
        }

        $this->authorize('inventories update');

        try {
            return response()->streamDownload(function () use ($transaction, $invoiceService) {
                $invoiceService->generateInvoicePdf(
                    $transaction,
                    true
                );
            });
        } catch (Exception $e) {
            Log::error($e->getTraceAsString());
            return $this->makeErrorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get Payment Receipt pdf file
     *
     * @param Transaction $transaction
     * @param InvoiceService $invoiceService
     * @return JsonResponse|StreamedResponse
     *
     * @OA\Get(
     *     path="/api/body-shop/inventories/transactions/{transactionId}/generate-payment-receipt",
     *     tags={"Inventories Body Shop"},
     *     summary="Get Payment Receipt pdf file",
     *     operationId="Get Payment Receipt pdf file",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="id", in="path", description="Transaction id", required=true,
     *          @OA\Schema(type="integer",)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *     ),
     * )
     * @todo moved
     */
    public function generatePaymentReceipt(Transaction $transaction,InvoiceService $invoiceService)
    {
        if ($transaction->describe !== Transaction::DESCRIBE_SOLD && !$transaction->isSold()) {
            return $this->makeErrorResponse(null, Response::HTTP_NOT_FOUND);
        }

        $this->authorize('inventories update');

        try {
            return response()->streamDownload(function () use ($transaction, $invoiceService) {
                $invoiceService->generatePaymentReceiptPdf(
                    $transaction,
                    true
                );
            });
        } catch (Exception $e) {
            Log::error($e->getTraceAsString());
            return $this->makeErrorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @OA\Delete(
     *     path="/api/body-shop/inventories/{inventoryId}",
     *     tags={"Inventories Body Shop"},
     *     summary="Delete inventory",
     *     operationId="Delete inventory",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="id", in="path", description="Inventory id", required=true,
     *          @OA\Schema(type="integer", default="1",)
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Successful operation",
     *     ),
     * )
     * @param Inventory $inventory
     * @return JsonResponse
     * @throws AuthorizationException
     * @todo moved
     */
    public function destroy(Inventory $inventory)
    {
        $this->authorize('inventories delete');

        try {
            $this->service->destroy($inventory);

            return $this->makeSuccessResponse(null, Response::HTTP_NO_CONTENT);
        } catch (HasRelatedEntitiesException $e) {
            if ($inventory->isInStock()) {
                return $this->makeErrorResponse(
                    trans(
                        'This part is not out of stock and can not be deleted.',
                    ),
                    Response::HTTP_UNPROCESSABLE_ENTITY
                );
            }

            $message = trans('This part is used in ') . '%s.';
            $usedIn = [];
            if ($inventory->hasRelatedOpenOrders()) {
                $url = str_replace('{id}', $inventory->id, config('frontend.bs_open_orders_with_inventory_filter_url'));
                $usedIn[] = '<a href="' . $url . '">' . trans('open orders') . '</a>';
            }

            if ($inventory->hasRelatedDeletedOrders()) {
                $url = str_replace('{id}', $inventory->id, config('frontend.bs_deleted_orders_with_inventory_filter_url'));
                $usedIn[] = '<a href="' . $url . '">' . trans('deleted orders') . '</a>';
            }

            if ($usedIn) {
                $message .= trans(' Please delete order permanently first.');
            }

            if ($inventory->hasRelatedTypesOfWork()) {
                $url = str_replace('{id}', $inventory->id, config('frontend.bs_types_of_work_with_inventory_filter_url'));
                $usedIn[] = '<a href="' . $url . '">types of work</a>';
            }

            return $this->makeErrorResponse(
                sprintf($message, implode(trans(' and '), $usedIn)),
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        } catch (Exception $e) {
            Log::error($e);

            return $this->makeErrorResponse(null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/body-shop/inventories/export",
     *     tags={"Inventory Body Shop Export"},
     *     summary="Returns a link to download excel file",
     *     operationId="InventoriesExcelFile",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *
     *     @OA\Parameter(name="q", in="query", description="Scope for filter by name, stock number", required=false,
     *         @OA\Schema( type="string", default="name",)
     *     ),
     *     @OA\Parameter(name="category_id", in="query", description="Category id", required=false,
     *         @OA\Schema(type="integer", default="1")
     *     ),
     *     @OA\Parameter(name="supplier_id", in="query", description="Supplier id", required=false,
     *         @OA\Schema(type="integer", default="1")
     *     ),
     *     @OA\Parameter(name="status", in="query", description="Status", required=false,
     *         @OA\Schema(type="string", default="1", enum={"in_stock", "out_of_stock"})
     *     ),
     *     @OA\Parameter(name="only_min_limit", in="query", description="Resc only min limit", required=false,
     *         @OA\Schema(type="bool", default="true")
     *     ),
     *     @OA\Parameter(name="for_sale", in="query", description="For sale", required=false,
     *         @OA\Schema(type="bool", default="true")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
     *     )
     * )
     * @todo moved
     */
    public function export(InventoryIndexRequest $request): JsonResponse
    {
        $this->authorize('inventories');

        try {
            $models = Inventory::query()
                ->filter($request->validated())
                ->with(['category'])
                ->orderByRaw('CASE WHEN quantity = 0 THEN 0 ELSE 1 END desc, name asc')
                ->get();

            $time = CarbonImmutable::now()->format('Y-m-d_H-i-s');
//            $time = CarbonImmutable::now()->timestamp;

            $name = "excel/List_of_parts_as_at_{$time}.xlsx";

            if(Storage::disk('public')->exists($name)){
                Storage::disk('public')->delete($name);
            }

            Excel::store(new InventoryExport($models), $name,'public');

            $link = url("/storage/{$name}");

            return $this->makeSuccessResponse($link, 200);
        } catch (\Exception $e) {
            return $this->makeErrorResponse($e->getMessage(), $e->getCode());
        }
    }
}
