<?php

namespace App\Http\Controllers\Api\V1\Inventories\Inventory;

use App\Enums\Inventories\Transaction\DescribeType;
use App\Enums\Inventories\Transaction\OperationType;
use App\Enums\Inventories\Transaction\PaymentMethod;
use App\Foundations\Enums\EnumHelper;
use App\Http\Controllers\Api\ApiController;
use App\Foundations\Modules\Permission\Permissions as Permission;
use App\Http\Requests\Inventories\Transaction\PurchaseRequest;
use App\Http\Requests\Inventories\Transaction\SoldRequest;
use App\Http\Requests\Inventories\Transaction\TransactionIndexRequest;
use App\Http\Requests\Inventories\Transaction\TransactionReportRequest;
use App\Http\Resources\Inventories\Transaction\PaymentMethodResource;
use App\Http\Resources\Inventories\Transaction\TransactionPaginationResource;
use App\Http\Resources\Inventories\Transaction\TransactionReportPaginateResource;
use App\Http\Resources\Inventories\Transaction\TransactionReportTotalResource;
use App\Http\Resources\Inventories\Transaction\TransactionResource;
use App\Models\Inventories\Inventory;
use App\Models\Inventories\Transaction;
use App\Repositories\Inventories\TransactionRepository;
use App\Services\Inventories\InventoryTransactionService;
use App\Services\Inventories\InvoiceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TransactionController extends ApiController
{
    public function __construct(
        protected TransactionRepository $repo,
        protected InventoryTransactionService $service,
        protected InvoiceService $invoiceService
    )
    {}

    /**
     * @OA\Get(
     *     path="/api/v1/inventories/{id}/transactions",
     *     tags={"Inventory transactions"},
     *     security={{"Basic": {}}},
     *     summary="Get inventory transaction pagination list",
     *     operationId="GetInventoryTransactionPaginationList",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *
     *     @OA\Parameter(ref="#/components/parameters/IDPath"),
     *
     *     @OA\Parameter(ref="#/components/parameters/Page"),
     *     @OA\Parameter(ref="#/components/parameters/PerPage"),
     *
     *     @OA\Response(response=200, description="Inventory transaction data",
     *         @OA\JsonContent(ref="#/components/schemas/InventoryTransactionPaginationResource")
     *     ),
     *
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function index(TransactionIndexRequest $request, $id): ResourceCollection
    {
        $this->authorize(Permission\Inventory\Inventory\InventoryReadTransactionPermission::KEY);

        $filters = $request->validated();
        $filters['inventory_id'] = $id;

        return TransactionPaginationResource::collection(
            $this->repo->getCustomPagination(
                filters: $filters,
            )
        );
    }

    /**
     * @OA\Get (
     *     path="/api/v1/inventories/{id}/reserve",
     *     tags={"Inventory transactions"},
     *     security={{"Basic": {}}},
     *     summary="Get inventory transaction reserved pagination list",
     *     operationId="GetInventoryTransactionReservedPaginationList",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *
     *     @OA\Parameter(ref="#/components/parameters/IDPath"),
     *
     *     @OA\Parameter(ref="#/components/parameters/Page"),
     *     @OA\Parameter(ref="#/components/parameters/PerPage"),
     *
     *     @OA\Response(response=200, description="Inventory transaction data",
     *         @OA\JsonContent(ref="#/components/schemas/InventoryTransactionPaginationResource")
     *     ),
     *
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function reserved(TransactionIndexRequest $request, $id): ResourceCollection
    {
        $this->authorize(Permission\Inventory\Inventory\InventoryReadTransactionPermission::KEY);

        $filters = $request->validated();
        $filters['inventory_id'] = $id;
        $filters['operation_type'] = OperationType::SOLD->value;

        return TransactionPaginationResource::collection(
            $this->repo->getReservedCustomPagination(
                filters: $filters,
            )
        );
    }

    /**
     * @OA\Post(
     *     path="/api/v1/inventories/{id}/purchase",
     *     tags={"Inventory transactions"},
     *     security={{"Basic": {}}},
     *     summary="Create purchase inventory",
     *     operationId="CreatePurchaseInventory",
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
     *         @OA\JsonContent(ref="#/components/schemas/PurchaseRequest")
     *     ),
     *
     *     @OA\Response(response=200, description="Inventory transaction data",
     *         @OA\JsonContent(ref="#/components/schemas/InventoryTransactionResource")
     *     ),
     *
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="404", description="Not Found", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="422", description="Validation", @OA\JsonContent(ref="#/components/schemas/ValidationErrors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function purchase(PurchaseRequest $request): TransactionResource
    {
        $this->authorize(Permission\Inventory\Inventory\InventoryCreateTransactionPermission::KEY);

        return TransactionResource::make(
            $this->service->create($request->getModel(), $request->getDto())
        );
    }

    /**
     * @OA\Post(
     *     path="/api/v1/inventories/{id}/sold",
     *     tags={"Inventory transactions"},
     *     security={{"Basic": {}}},
     *     summary="Sold inventory",
     *     operationId="SoldInventory",
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
     *         @OA\JsonContent(ref="#/components/schemas/SoldRequest")
     *     ),
     *
     *     @OA\Response(response=200, description="Inventory transaction data",
     *         @OA\JsonContent(ref="#/components/schemas/InventoryTransactionResource")
     *     ),
     *
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="404", description="Not Found", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="422", description="Validation", @OA\JsonContent(ref="#/components/schemas/ValidationErrors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function sold(SoldRequest $request): TransactionResource
    {
        $this->authorize(Permission\Inventory\Inventory\InventoryCreateTransactionPermission::KEY);

        return TransactionResource::make(
            $this->service->create($request->getModel(), $request->getDto())
        );
    }

    /**
     * @OA\Get(
     *     path="/v1/inventories/payment-methods",
     *     tags={"Inventory transactions"},
     *     summary="Get payment methods list for inventory transactions",
     *     operationId="GetPaymentMethodsListForInventoryTransactions",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *
     *     @OA\Response(response=200, description="Payment method data",
     *         @OA\JsonContent(ref="#/components/schemas/PaymentMethodResource")
     *     ),
     *
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     *
     */
    public function paymentMethod(): AnonymousResourceCollection
    {
        return PaymentMethodResource::collection(
            EnumHelper::resourceList(PaymentMethod::class)
        );
    }

    /**
     * @OA\Get(
     *     path="/api/v1/inventories/report",
     *     tags={"Inventory transactions"},
     *     security={{"Basic": {}}},
     *     summary="Get inventory report ",
     *     operationId="GetInventoryReport",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *
     *     @OA\Parameter(ref="#/components/parameters/Page"),
     *     @OA\Parameter(ref="#/components/parameters/PerPage"),
     *
     *     @OA\Parameter(name="search", in="query", description="Search string", required=false,
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
     *
     *     @OA\Response(response=200, description="Inventory Report data",
     *         @OA\JsonContent(ref="#/components/schemas/InventoryTransactionReportPaginate"),
     *     ),
     *
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="404", description="Not Found", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="422", description="Validation", @OA\JsonContent(ref="#/components/schemas/ValidationErrors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function transactionsReport(TransactionReportRequest $request): AnonymousResourceCollection
    {
        $this->authorize(Permission\Report\ReportInventoryPermission::KEY);

        return TransactionReportPaginateResource::collection(
            $this->repo->getForReport($request->validated())
        );
    }

    /**
     * @OA\Get(
     *     path="/api/v1/inventories/report-total",
     *     tags={"Inventory transactions"},
     *     security={{"Basic": {}}},
     *     summary="Get inventory report total",
     *     operationId="GetInventoryReportTotal",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *
     *     @OA\Parameter(name="search", in="query", description="Search string", required=false,
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
     *
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/TransactionsReportTotal"),
     *     ),
     *
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="404", description="Not Found", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="422", description="Validation", @OA\JsonContent(ref="#/components/schemas/ValidationErrors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function transactionsReportTotal(TransactionReportRequest $request): TransactionReportTotalResource
    {
        $this->authorize(Permission\Report\ReportInventoryPermission::KEY);

        $priceWithDiscountAndTax = 'ROUND(price - (price * coalesce(discount, 0) / 100) + ((price - (price *  coalesce(discount, 0) / 100)) *  coalesce(tax, 0) / 100), 2)';
        $total = Transaction::query()
            ->selectRaw('
                SUM(quantity * CASE WHEN operation_type = \'sold\' THEN ' . $priceWithDiscountAndTax . ' ELSE 0 END) as price_total,
                SUM(quantity * CASE WHEN operation_type = \'purchase\' THEN ' . $priceWithDiscountAndTax . ' ELSE 0 END) as cost_total
            ')
            ->filter($request->validated())
            ->where('is_reserve', false)
            ->first();

        return TransactionReportTotalResource::make($total);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/inventories/transactions/{id}/generate-invoice",
     *     tags={"Inventory transactions"},
     *     security={{"Basic": {}}},
     *     summary="Get Invoice pdf file",
     *     operationId="GetInvoicePdfFile",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *
     *     @OA\Parameter(name="id", in="path", description="Transaction id", required=true,
     *          @OA\Schema(type="integer",)
     *     ),
     *
     *     @OA\Response(response=200, description="Successful operation"),
     *
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function generateInvoice($id): StreamedResponse|JsonResponse
    {
        /** @var $model Transaction */
        $model = $this->repo->getBy(['id' => $id],
            withException: true,
            exceptionMessage: __("exceptions.inventories.transaction.not_found")
        );

        if ($model->describe !== DescribeType::Sold->value && !$model->operation_type->isSold()) {
            return $this->errorJsonMessage(null, Response::HTTP_NOT_FOUND);
        }

        $this->authorize(Permission\Inventory\Inventory\InventoryUpdatePermission::KEY);

        return response()->streamDownload(function () use ($model) {
            $this->invoiceService->generateInvoicePdf(
                $model,
                true
            );
        });
    }

    /**
     * @OA\Get(
     *     path="/api/v1/inventories/transactions/{id}/generate-payment-receipt",
     *     tags={"Inventory transactions"},
     *     security={{"Basic": {}}},
     *     summary="Get Payment Receipt pdf file",
     *     operationId="GetPaymentReceiptPdfFile",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *
     *     @OA\Parameter(name="id", in="path", description="Transaction id", required=true,
     *         @OA\Schema(type="integer",)
     *     ),
     *
     *     @OA\Response(response=200, description="Successful operation",),
     *
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function generatePaymentReceipt($id): StreamedResponse|JsonResponse
    {
        /** @var $model Transaction */
        $model = $this->repo->getBy(['id' => $id],
            withException: true,
            exceptionMessage: __("exceptions.inventories.transaction.not_found")
        );

        if ($model->describe !== DescribeType::Sold->value && !$model->operation_type->isSold()) {
            return $this->errorJsonMessage(null, Response::HTTP_NOT_FOUND);
        }

        $this->authorize(Permission\Inventory\Inventory\InventoryUpdatePermission::KEY);

        return response()->streamDownload(function () use ($model) {
                $this->invoiceService->generatePaymentReceiptPdf(
                    $model,
                    true
                );
            });

    }
}
