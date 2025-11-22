<?php

namespace App\Http\Controllers\Api\Orders;

use App\Dto\Orders\OrderIndexDto;
use App\Exceptions\Contact\SenderDoesNotHaveEmail;
use App\Exceptions\Order\ChangeOrderStatusException;
use App\Exceptions\Order\EmptyInvoiceTotalDue;
use App\Exceptions\Order\HaveToAgreeWithInspection;
use App\Exceptions\Order\OrderAlreadySigned;
use App\Exceptions\Order\OrderCantBeMovedOffers;
use App\Exceptions\Order\OrderHasNotHadInspectionYet;
use App\Exceptions\Order\OrderSignatureLinkExpired;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Orders\AssignDriverRequest;
use App\Http\Requests\Orders\ChangeDeductFlagRequest;
use App\Http\Requests\Orders\ChangeOrderStatusRequest;
use App\Http\Requests\Orders\DeleteVehicleRequest;
use App\Http\Requests\Orders\DownloadInvoiceRequest;
use App\Http\Requests\Orders\OrderHistoryRequest;
use App\Http\Requests\Orders\OrderIndexRequest;
use App\Http\Requests\Orders\OrderRequest;
use App\Http\Requests\Orders\OrdersExportRequest;
use App\Http\Requests\Orders\PaymentStageRequest;
use App\Http\Requests\Orders\PublicInvoiceRequest;
use App\Http\Requests\Orders\SameLoadIdRequest;
use App\Http\Requests\Orders\SameVinRequest;
use App\Http\Requests\Orders\SendDocsRequest;
use App\Http\Requests\Orders\SendSignatureLinkRequest;
use App\Http\Requests\Orders\SignPublicBolRequest;
use App\Http\Requests\Orders\SingleAttachmentRequest;
use App\Http\Requests\Orders\SingleExpenseRequest;
use App\Http\Requests\Orders\SingleVehicleRequest;
use App\Http\Requests\Orders\SplitOrderRequest;
use App\Http\Requests\Orders\TakeOfferRequest;
use App\Http\Resources\Contacts\ContactTypesListResource;
use App\Http\Resources\History\HistoryListResource;
use App\Http\Resources\History\HistoryPaginatedResource;
use App\Http\Resources\Orders\ExpenseTypesListResource;
use App\Http\Resources\Orders\OrderAvailableInvoicesResource;
use App\Http\Resources\Orders\OrderBolResource;
use App\Http\Resources\Orders\OrderPaginatedResource;
use App\Http\Resources\Orders\OrderResource;
use App\Http\Resources\Orders\OrderTotalResource;
use App\Http\Resources\Orders\PaymentStageResource;
use App\Http\Resources\Orders\SameVinOrLoadIdResource;
use App\Http\Resources\Orders\VehiclesFilterListResource;
use App\Http\Resources\Orders\VehicleTypesListResource;
use App\Http\Resources\Users\DriversListResource;
use App\Models\History\History;
use App\Models\Orders\Bonus;
use App\Models\Orders\Expense;
use App\Models\Orders\Order;
use App\Models\Orders\PaymentStage;
use App\Models\Orders\Vehicle;
use App\Models\Users\User;
use App\Services\Contacts\ContactService;
use App\Services\Orders\CompanySearchService;
use App\Services\Orders\GeneratePdfService;
use App\Services\Orders\OrderPhotoService;
use App\Services\Orders\OrderSearchService;
use App\Services\Orders\OrderService;
use App\Services\Orders\OrderStatusService;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Log;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Throwable;

class OrderController extends ApiController
{
    private OrderService $orderService;

    private ?OrderStatusService $orderStatusService = null;

    public function __construct(OrderService $orderService)
    {
        parent::__construct();

        $this->orderService = $orderService->setUser(authUser());
    }

    private function statusService(): OrderStatusService
    {
        if (!empty($this->orderStatusService)) {
            return $this->orderStatusService;
        }
        $this->orderStatusService = resolve(OrderStatusService::class)->setUser(authUser());

        return $this->orderStatusService;
    }

    /**
     * @param Order $order
     * @return AnonymousResourceCollection
     * @OA\Get(
     *     path="/api/orders/{orderId}/get-drivers-list",
     *     tags={"Orders"},
     *     summary="Get drivers list",
     *     operationId="Get drivers list",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/DriversListResource")
     *     ),
     * )
     */
    public function getDriversForOrder(Order $order): AnonymousResourceCollection
    {
        /**@var User $driversQuery*/
        $driversQuery = User::with(['roles']);

        if ($order->dispatcher_id) {
            $driversQuery->where('owner_id', $order->dispatcher_id);
        } elseif (!request()->user()->isAdmin()) {
            $driversQuery->where('owner_id', request()->user()->id);
        }

        $driversQuery->onlyDrivers()
            ->withoutSuperDrivers()
            ->orderBy('full_name');

        $drivers = $driversQuery->get();

        return DriversListResource::collection($drivers);
    }

    /**
     *
     * @param ContactService $contactService
     * @return AnonymousResourceCollection
     *
     * @OA\Get(
     *     path="/api/orders/contact-types",
     *     tags={"Orders"},
     *     summary="Get contact types list",
     *     operationId="Get contact types",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/ContactTypesListResource")
     *     ),
     * )
     */
    public function contactTypes(ContactService $contactService): AnonymousResourceCollection
    {
        return ContactTypesListResource::collection(
            $contactService->getContactTypesForOrder()
        );
    }

    /**
     *
     * @return AnonymousResourceCollection
     *
     * @OA\Get(
     *     path="/api/orders/vehicle-types",
     *     tags={"Orders"},
     *     summary="Get vehicle types list",
     *     operationId="Get vehicle types",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/VehicleTypesListResource")
     *     ),
     * )
     *
     */
    public function vehicleTypes(): AnonymousResourceCollection
    {
        return VehicleTypesListResource::collection(
            Vehicle::getTypesList()
        );
    }

    /**
     *
     * @return AnonymousResourceCollection
     *
     * @OA\Get(
     *     path="/api/orders/expense-types",
     *     tags={"Orders"},
     *     summary="Get expense types list",
     *     operationId="Get expense types",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/ExpenseTypesListResource")
     *     ),
     * )
     *
     */
    public function expenseTypes(): AnonymousResourceCollection
    {
        return ExpenseTypesListResource::collection(
            Expense::getTypesList()
        );
    }

    /**
     *
     * @return AnonymousResourceCollection
     *
     * @OA\Get(
     *     path="/api/orders/vehicles-for-filter",
     *     tags={"Orders"},
     *     summary="Get vehicles list for order filter",
     *     operationId="Get vehicles list for order filter",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/VehiclesFilterListResource")
     *     ),
     * )
     *
     */
    public function vehiclesForFilter(): AnonymousResourceCollection
    {
        return VehiclesFilterListResource::collection(
            $this->orderService->vehiclesForFilter()
        );
    }

    /**
     * @param SameLoadIdRequest $request
     * @param OrderSearchService $service
     * @return AnonymousResourceCollection
     *
     * @OA\Get(
     *     path="/api/orders/same-load-id",
     *     tags={"Orders"},
     *     summary="Get orders with the same load id",
     *     operationId="Get orders with the same load id",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(
     *          name="order_id",
     *          in="query",
     *          description="",
     *          required=false,
     *          @OA\Schema(
     *              type="integer",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="load_id",
     *          in="query",
     *          description="",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *          )
     *     ),
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/SameVinOrLoadIdResource")
     *     ),
     * )
     */
    public function sameLoadId(SameLoadIdRequest $request, OrderSearchService $service): AnonymousResourceCollection
    {
        return SameVinOrLoadIdResource::collection(
            $service->searchSameLoadId(
                $request->getLoadId(),
                $request->getOrderId()
            )
        );
    }

    /**
     * @param SameVinRequest $request
     * @param OrderSearchService $service
     * @return AnonymousResourceCollection
     *
     * @OA\Get(
     *     path="/api/orders/same-vin",
     *     tags={"Orders"},
     *     summary="Get orders with the same vehicle vin",
     *     operationId="Get orders with the same vehicle vin",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(
     *          name="order_id",
     *          in="query",
     *          description="",
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
     *         @OA\JsonContent(ref="#/components/schemas/SameVinOrLoadIdResource")
     *     ),
     * )
     */
    public function sameVin(SameVinRequest $request, OrderSearchService $service): AnonymousResourceCollection
    {
        return SameVinOrLoadIdResource::collection(
            $service->searchSameVin($request->getVin(), $request->getOrderId())
        );
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return AnonymousResourceCollection
     *
     * @OA\Get(
     *     path="/api/orders/offers",
     *     tags={"Orders"},
     *     summary="Get offers paginated list",
     *     operationId="Get offers data",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(
     *          name="has_review",
     *          in="query",
     *          description="If order was reviewed",
     *          required=false,
     *          @OA\Schema(
     *              type="boolean",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="s",
     *          in="query",
     *          description="Search string",
     *          required=false,
     *          @OA\Schema(
     *              type="string",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="make",
     *          in="query",
     *          description="Vehicle Make",
     *          required=false,
     *          @OA\Schema(
     *              type="string",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="model",
     *          in="query",
     *          description="Vehicle Model",
     *          required=false,
     *          @OA\Schema(
     *              type="string",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="year",
     *          in="query",
     *          description="Vehicle Year",
     *          required=false,
     *          @OA\Schema(
     *              type="string",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="driver_id",
     *          in="query",
     *          description="Driver user id",
     *          required=false,
     *          @OA\Schema(
     *              type="integer",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="dispatcher_id",
     *          in="query",
     *          description="Dispatcher user id",
     *          required=false,
     *          @OA\Schema(
     *              type="integer",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="page",
     *          in="query",
     *          description="Page number",
     *          required=false,
     *          @OA\Schema(
     *              type="integer",
     *              default="5"
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="per_page",
     *          in="query",
     *          description="Orders per page",
     *          required=false,
     *          @OA\Schema(
     *              type="integer",
     *              default="10"
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="order_by",
     *          in="query",
     *          description="Field to sort by",
     *          required=false,
     *          @OA\Schema(
     *              type="string",
     *              default="id",
     *              enum ={"id","load_id"}
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="order_type",
     *          in="query",
     *          description="Sort order",
     *          required=false,
     *          @OA\Schema(
     *              type="string",
     *              default="asc",
     *              enum ={"asc","desc"}
     *          )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/OrderPaginatedResource")
     *     ),
     * )
     *
     * @throws AuthorizationException
     */
    public function offers(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewList', Order::class);

        $dto = OrderIndexDto::create(
            array_merge(
                $request
                    ->only(
                        [
                            'make',
                            'model',
                            'year',
                            'driver_id',
                            'dispatcher_id',
                            'has_review',
                            's'
                        ]
                    ),
                [
                    'state' => [
                        Order::CALCULATED_STATUS_OFFER
                    ],
                    'page' => $request->get('page', 1),
                    'per_page' => $request->get('per_page', 10),
                    'order_by' => in_array($request->input('order_by'), ['id', 'load_id']) ? $request->input('order_by') : 'id',
                    'order_type' => in_array($request->input('order_type'), ['asc', 'desc']) ? $request->input('order_type') : 'desc'
                ]
            )
        );

        return OrderPaginatedResource::collection(
            $this->orderService->getOrderList($dto)
        );
    }

    /**
     * Display a listing of the resource.
     *
     * @param OrderIndexRequest $request
     * @return AnonymousResourceCollection
     *
     * @OA\Get (
     *     path="/api/orders/",
     *     tags={"Orders"},
     *     summary="Get orders paginated list",
     *     operationId="Get orders data",
     *     deprecated=false,
     *     @OA\Parameter (ref="#/components/parameters/Content-type"),
     *     @OA\Parameter (ref="#/components/parameters/Accept"),
     *     @OA\Parameter (ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter (ref="#/components/parameters/Authorization"),
     *     @OA\Parameter (
     *          name="state",
     *          in="query",
     *          description="Order status (new, assigned, pickedup, delivered, deleted)",
     *          required=false,
     *          @OA\Schema (
     *              type="array",
     *              @OA\Items (
     *                  allOf={
     *                      @OA\Schema (
     *                          type="string",
     *                          enum={
     *                              "new",
     *                              "assigned",
     *                              "pickedup",
     *                              "delivered",
     *                              "deleted"
     *                          }
     *                      )
     *                  }
     *              )
     *          )
     *     ),
     *     @OA\Parameter (
     *          name="dashboard_filter",
     *          in="query",
     *          description="Show orders from dashboard",
     *          required=false,
     *          @OA\Schema (
     *              type="string",
     *              enum={
     *                  "today_delivered_orders",
     *                  "today_paid_orders",
     *                  "pickup_overdue_orders",
     *                  "delivery_overdue_orders",
     *                  "today_pickup_orders",
     *                  "today_delivery_orders",
     *                  "payment_overdue_orders",
     *                  "today_paid_orders",
     *                  "month_paid_orders"
     *              }
     *          )
     *     ),
     *     @OA\Parameter (
     *          name="has_review",
     *          in="query",
     *          description="If order was reviewed",
     *          required=false,
     *          @OA\Schema (
     *              type="boolean"
     *          )
     *     ),
     *     @OA\Parameter (
     *          name="has_broker_fee",
     *          in="query",
     *          description="If order has broker fee payment data",
     *          required=false,
     *          @OA\Schema (
     *              type="boolean"
     *          )
     *     ),
     *     @OA\Parameter (
     *          name="s",
     *          in="query",
     *          description="Search string",
     *          required=false,
     *          @OA\Schema (
     *              type="string"
     *          )
     *     ),
     *     @OA\Parameter (
     *          name="make",
     *          in="query",
     *          description="Vehicle Make",
     *          required=false,
     *          @OA\Schema (
     *              type="string"
     *          )
     *     ),
     *     @OA\Parameter (
     *          name="model",
     *          in="query",
     *          description="Vehicle Model",
     *          required=false,
     *          @OA\Schema (
     *              type="string"
     *          )
     *     ),
     *     @OA\Parameter (
     *          name="year",
     *          in="query",
     *          description="Vehicle Year",
     *          required=false,
     *          @OA\Schema (
     *              type="string"
     *          )
     *     ),
     *     @OA\Parameter (
     *          name="driver_id",
     *          in="query",
     *          description="Driver user id",
     *          required=false,
     *          @OA\Schema (
     *              type="integer"
     *          )
     *     ),
     *     @OA\Parameter (
     *          name="dispatcher_id",
     *          in="query",
     *          description="Dispatcher user id",
     *          required=false,
     *          @OA\Schema (
     *              type="integer"
     *          )
     *     ),
     *     @OA\Parameter (
     *          name="attributes",
     *          in="query",
     *          description="additional order attributes",
     *          required=false,
     *          @OA\Schema (
     *              type="array",
     *              @OA\Items (
     *                  allOf={
     *                      @OA\Schema (
     *                          type="string",
     *                          enum={
     *                              "overdue",
     *                              "not_overdue",
     *                              "billed",
     *                              "not_billed",
     *                              "paid",
     *                              "not_paid",
     *                              "reviewed",
     *                              "not_reviewed",
     *                              "broker_fee_paid",
     *                              "broker_fee_not_paid"
     *                          }
     *                      )
     *                  }
     *              )
     *          )
     *     ),
     *     @OA\Parameter (
     *          name="invoice_id",
     *          in="query",
     *          description="Filter by customer/broker invoice ID",
     *          required=false,
     *          @OA\Schema (
     *              type="string"
     *          )
     *     ),
     *     @OA\Parameter (
     *          name="check_id",
     *          in="query",
     *          description="Filter by reference/uship number",
     *          required=false,
     *          @OA\Schema (
     *              type="string"
     *          )
     *     ),
     *     @OA\Parameter (
     *          name="company_name",
     *          in="query",
     *          description="Filter by broker (shipper) company name",
     *          required=false,
     *          @OA\Schema (
     *              type="string"
     *          )
     *     ),
     *     @OA\Parameter (
     *          name="date_from",
     *          in="query",
     *          description="Date from",
     *          required=false,
     *          @OA\Schema (
     *              type="string",
     *          )
     *     ),
     *     @OA\Parameter (
     *          name="date_to",
     *          in="query",
     *          description="Date to",
     *          required=false,
     *          @OA\Schema(
     *              type="string",
     *          )
     *     ),
     *     @OA\Parameter (
     *          name="date_type",
     *          in="query",
     *          description="Date type",
     *          required=false,
     *          @OA\Schema(
     *              type="string",
     *              enum={
     *                  "pickup",
     *                  "delivery",
     *                  "invoice-sent",
     *                  "created_at",
     *                  "paid_at"
     *              }
     *          )
     *     ),
     *     @OA\Parameter (
     *          name="page",
     *          in="query",
     *          description="Page number",
     *          required=false,
     *          @OA\Schema (
     *              type="integer",
     *              default="1"
     *          )
     *     ),
     *     @OA\Parameter (
     *          name="per_page",
     *          in="query",
     *          description="Orders per page",
     *          required=false,
     *          @OA\Schema (
     *              type="integer",
     *              default="12"
     *          )
     *     ),
     *     @OA\Parameter (
     *          name="order_by",
     *          in="query",
     *          description="Orders sorting field (total_due, current_due, past_due)",
     *          required=false,
     *          @OA\Schema (
     *              type="string",
     *              default="total_due"
     *          )
     *     ),
     *     @OA\Parameter (
     *          name="order_type",
     *          in="query",
     *          description="Orders type (asc, desc)",
     *          required=false,
     *          @OA\Schema (
     *              type="string",
     *              default="asc"
     *          )
     *     ),
     *     @OA\Response (response=200, description="Successful operation",
     *         @OA\JsonContent (ref="#/components/schemas/OrderPaginatedResource")
     *     ),
     * )
     */
    public function index(OrderIndexRequest $request): AnonymousResourceCollection
    {
        return OrderPaginatedResource::collection(
            $this->orderService->getOrderList($request->dto())
        );
    }

    /**
     * Display a listing of the resource.
     *
     * @param OrderIndexRequest $request
     * @return OrderTotalResource
     *
     * @OA\Get (
     *     path="/api/orders/total",
     *     tags={"Orders"},
     *     summary="Get orders total data",
     *     operationId="Get orders total data",
     *     deprecated=false,
     *     @OA\Parameter (ref="#/components/parameters/Content-type"),
     *     @OA\Parameter (ref="#/components/parameters/Accept"),
     *     @OA\Parameter (ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter (ref="#/components/parameters/Authorization"),
     *     @OA\Parameter (
     *          name="state",
     *          in="query",
     *          description="Order status (new, assigned, pickedup, delivered, deleted)",
     *          required=false,
     *          @OA\Schema (
     *              type="array",
     *              @OA\Items (
     *                  allOf={
     *                      @OA\Schema (
     *                          type="string",
     *                          enum={
     *                              "new",
     *                              "assigned",
     *                              "pickedup",
     *                              "delivered",
     *                              "deleted"
     *                          }
     *                      )
     *                  }
     *              )
     *          )
     *     ),
     *     @OA\Parameter (
     *          name="dashboard_filter",
     *          in="query",
     *          description="Show orders from dashboard",
     *          required=false,
     *          @OA\Schema (
     *              type="string",
     *              enum={
     *                  "today_delivered_orders",
     *                  "today_paid_orders",
     *                  "pickup_overdue_orders",
     *                  "delivery_overdue_orders",
     *                  "today_pickup_orders",
     *                  "today_delivery_orders",
     *                  "payment_overdue_orders",
     *                  "today_paid_orders",
     *                  "month_paid_orders"
     *              }
     *          )
     *     ),
     *     @OA\Parameter (
     *          name="has_review",
     *          in="query",
     *          description="If order was reviewed",
     *          required=false,
     *          @OA\Schema (
     *              type="boolean"
     *          )
     *     ),
     *     @OA\Parameter (
     *          name="has_broker_fee",
     *          in="query",
     *          description="If order has broker fee payment data",
     *          required=false,
     *          @OA\Schema (
     *              type="boolean"
     *          )
     *     ),
     *     @OA\Parameter (
     *          name="s",
     *          in="query",
     *          description="Search string",
     *          required=false,
     *          @OA\Schema (
     *              type="string"
     *          )
     *     ),
     *     @OA\Parameter (
     *          name="make",
     *          in="query",
     *          description="Vehicle Make",
     *          required=false,
     *          @OA\Schema (
     *              type="string"
     *          )
     *     ),
     *     @OA\Parameter (
     *          name="model",
     *          in="query",
     *          description="Vehicle Model",
     *          required=false,
     *          @OA\Schema (
     *              type="string"
     *          )
     *     ),
     *     @OA\Parameter (
     *          name="year",
     *          in="query",
     *          description="Vehicle Year",
     *          required=false,
     *          @OA\Schema (
     *              type="string"
     *          )
     *     ),
     *     @OA\Parameter (
     *          name="driver_id",
     *          in="query",
     *          description="Driver user id",
     *          required=false,
     *          @OA\Schema (
     *              type="integer"
     *          )
     *     ),
     *     @OA\Parameter (
     *          name="dispatcher_id",
     *          in="query",
     *          description="Dispatcher user id",
     *          required=false,
     *          @OA\Schema (
     *              type="integer"
     *          )
     *     ),
     *     @OA\Parameter (
     *          name="attributes",
     *          in="query",
     *          description="additional order attributes",
     *          required=false,
     *          @OA\Schema (
     *              type="array",
     *              @OA\Items (
     *                  allOf={
     *                      @OA\Schema (
     *                          type="string",
     *                          enum={
     *                              "overdue",
     *                              "not_overdue",
     *                              "billed",
     *                              "not_billed",
     *                              "paid",
     *                              "not_paid",
     *                              "reviewed",
     *                              "not_reviewed",
     *                              "broker_fee_paid",
     *                              "broker_fee_not_paid"
     *                          }
     *                      )
     *                  }
     *              )
     *          )
     *     ),
     *     @OA\Parameter (
     *          name="invoice_id",
     *          in="query",
     *          description="Filter by customer/broker invoice ID",
     *          required=false,
     *          @OA\Schema (
     *              type="string"
     *          )
     *     ),
     *     @OA\Parameter (
     *          name="check_id",
     *          in="query",
     *          description="Filter by reference/uship number",
     *          required=false,
     *          @OA\Schema (
     *              type="string"
     *          )
     *     ),
     *     @OA\Parameter (
     *          name="company_name",
     *          in="query",
     *          description="Filter by broker (shipper) company name",
     *          required=false,
     *          @OA\Schema (
     *              type="string"
     *          )
     *     ),
     *     @OA\Parameter (
     *          name="date_from",
     *          in="query",
     *          description="Date from",
     *          required=false,
     *          @OA\Schema (
     *              type="string",
     *          )
     *     ),
     *     @OA\Parameter (
     *          name="date_to",
     *          in="query",
     *          description="Date to",
     *          required=false,
     *          @OA\Schema(
     *              type="string",
     *          )
     *     ),
     *     @OA\Parameter (
     *          name="date_type",
     *          in="query",
     *          description="Date type",
     *          required=false,
     *          @OA\Schema(
     *              type="string",
     *              enum={
     *                  "pickup",
     *                  "delivery",
     *                  "invoice-sent",
     *                  "created_at",
     *                  "paid_at"
     *              }
     *          )
     *     ),
     *     @OA\Parameter (
     *          name="page",
     *          in="query",
     *          description="Page number",
     *          required=false,
     *          @OA\Schema (
     *              type="integer",
     *              default="1"
     *          )
     *     ),
     *     @OA\Parameter (
     *          name="per_page",
     *          in="query",
     *          description="Orders per page",
     *          required=false,
     *          @OA\Schema (
     *              type="integer",
     *              default="12"
     *          )
     *     ),
     *     @OA\Response (response=200, description="Successful operation",
     *         @OA\JsonContent (ref="#/components/schemas/OrderTotalResource")
     *     ),
     * )
     */
    public function orderTotal(OrderIndexRequest $request, OrderSearchService $service): OrderTotalResource
    {
        return OrderTotalResource::make(
            $service->getOrderTotal($request->dto()->getFilter())
        );
    }

    /**
     * @param Request $request
     * @return JsonResponse
     *
     * @OA\Get (
     *     path="/api/orders/companies-list",
     *     tags={"Orders"},
     *     summary="Get comapnies list (broker)",
     *     operationId="Get comapnies list (broker)",
     *     deprecated=false,
     *     @OA\Parameter (ref="#/components/parameters/Content-type"),
     *     @OA\Parameter (ref="#/components/parameters/Accept"),
     *     @OA\Parameter (ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter (ref="#/components/parameters/Authorization"),
     *     @OA\Parameter (
     *          name="company_name",
     *          in="query",
     *          description="Filter by comapny name",
     *          required=false,
     *          @OA\Schema (
     *              type="string"
     *          )
     *     ),
     *     @OA\Response (
     *          response=200,
     *          description="Successful operation",
     *          @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema (
     *                  type="object",
     *                  required={"data"},
     *                  @OA\Property (
     *                      property="data",
     *                      description="Response data",
     *                      type="object",
     *                      nullable=false,
     *                      allOf={
     *                          @OA\Schema (
     *                              type="object",
     *                              required={"companies"},
     *                              @OA\Property (
     *                                  property="companies",
     *                                  description="Comapnies list",
     *                                  type="array",
     *                                  nullable=false,
     *                                  @OA\Items (type="string")
     *                              )
     *                          )
     *                      }
     *                  )
     *              )
     *          )
     *     ),
     * )
     */
    public function orderCompanyList(Request $request, CompanySearchService $service): JsonResponse
    {
        return response()->json([
            'data' => [
                'companies' => $service->getCompanyList($request->input('company_name'))
            ]
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param OrderRequest $request
     * @return JsonResponse|OrderResource
     *
     * @OA\Post(
     *     path="/api/orders",
     *     tags={"Orders"},
     *     summary="Create order",
     *     operationId="Create order",
     *     deprecated=false,
     *     @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="appliaction/json",
     *              schema=@OA\Schema(ref="#/components/schemas/OrderRequest", schema="OrderRequestCreate")
     *          ),
     *     ),
     *     parameters={
     *          @OA\Parameter(ref="#/components/parameters/Content-type"),
     *          @OA\Parameter(ref="#/components/parameters/Accept"),
     *          @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *          @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     },
     *     responses={
     *          @OA\Response(
     *              response=200,
     *              description="Successful operation",
     *              @OA\JsonContent(ref="#/components/schemas/OrderResource")
     *          ),
     *     }
     * )
     *
     * @throws AuthorizationException
     * @throws Throwable
     */
    public function store(OrderRequest $request)
    {
        $this->authorize('create', Order::class);
        try {
            return OrderResource::make(
                $this
                    ->orderService
                    ->create($request->toDto())
                    ->loadMissingRelations()
            );
        } catch (Exception $e) {
            Log::error($e->getTraceAsString());
            return $this->makeErrorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param Order $order
     * @return OrderResource|JsonResponse
     *
     * @OA\Get(
     *     path="/api/orders/{orderId}",
     *     tags={"Orders"},
     *     summary="Get order info",
     *     operationId="Get order data",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/OrderResource")
     *     ),
     * )
     *
     * @throws AuthorizationException
     */
    public function show(Order $order)
    {
        $this->authorize('orders read');

        return OrderResource::make($order->loadMissingRelations());
    }

    /**
     * Update the specified resource in storage.
     *
     * @param OrderRequest $request
     * @param Order $order
     * @return OrderResource|JsonResponse
     *
     * @OA\Post(
     *     path="/api/orders/{orderId}/update",
     *     tags={"Orders"},
     *     summary="Update order",
     *     operationId="Update order",
     *     deprecated=false,
     *     @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="appliaction/json",
     *              schema=@OA\Schema(ref="#/components/schemas/OrderRequest", schema="OrderRequestUpdate")
     *          ),
     *     ),
     *     parameters={
     *          @OA\Parameter(ref="#/components/parameters/Content-type"),
     *          @OA\Parameter(ref="#/components/parameters/Accept"),
     *          @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *          @OA\Parameter(ref="#/components/parameters/Authorization"),
     *          @OA\Parameter(
     *               name="orderId",
     *               in="path",
     *               required=true,
     *               @OA\Schema(type="integer")
     *          ),
     *     },
     *     responses={
     *          @OA\Response(
     *              response=200,
     *              description="Successful operation",
     *              @OA\JsonContent(ref="#/components/schemas/OrderResource")
     *          ),
     *     }
     * )
     *
     * @throws AuthorizationException
     * @throws Throwable
     */
    public function update(OrderRequest $request, Order $order)
    {
        $this->authorize('update', $order);

        try {
            $order = $this->orderService->update(
                $order,
                $request->toDto()
            );
            $order = $this->statusService()->autoChangeStatus($order);

            return OrderResource::make($order->loadMissingRelations());
        } catch (Exception $e) {
            Log::error($e->getTraceAsString());
            return $this->makeErrorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Order $order
     * @return JsonResponse
     * @throws AuthorizationException
     * @OA\Delete(
     *     path="/api/orders/{orderId}",
     *     tags={"Orders"},
     *     summary="Delete order",
     *     operationId="Delete order",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Response(response=204, description="Successful operation"),
     * )
     */
    public function destroy(Order $order): JsonResponse
    {
        $this->authorize('delete', $order);

        try {
            $this->orderService->deleteOrder($order);
            return $this->makeSuccessResponse(null, Response::HTTP_NO_CONTENT);
        } catch (Exception $e) {
            Log::error($e->getTraceAsString());
            return $this->makeErrorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * View deleted order
     *
     * @param int $orderId
     * @return OrderResource|JsonResponse
     *
     * @throws AuthorizationException
     * @OA\Put(
     *     path="/api/orders/{orderId}/view-deleted",
     *     tags={"Orders"},
     *     summary="View deleted order",
     *     operationId="View deleted order",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/OrderResource")
     *     ),
     * )
     *
     */
    public function viewDeletedOrder(int $orderId)
    {
        $this->authorize('orders delete');

        if (!($order = Order::withTrashed()->find($orderId))) {
            return $this->makeErrorResponse(null, Response::HTTP_NOT_FOUND);
        }

        try {
            return OrderResource::make($order->loadMissingRelations());
        } catch (Exception $e) {
            Log::error($e->getTraceAsString());
            return $this->makeErrorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Restore deleted order
     *
     * @param int $orderId
     * @return OrderResource|JsonResponse
     *
     * @throws AuthorizationException
     * @OA\Put(
     *     path="/api/orders/{orderId}/restore",
     *     tags={"Orders"},
     *     summary="Restore deleted order",
     *     operationId="Restore deleted order",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/OrderResource")
     *     ),
     * )
     */
    public function restoreOrder(int $orderId)
    {
        if (!($order = Order::withTrashed()->find($orderId))) {
            return $this->makeErrorResponse(null, Response::HTTP_NOT_FOUND);
        }

        $this->authorize('restore', $order);

        try {
            return OrderResource::make(
                $this->orderService->restoreOrder($order)->loadMissingRelations()
            );
        } catch (Exception $e) {
            Log::error($e->getTraceAsString());
            return $this->makeErrorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Delete order permanently
     *
     * @param int $orderId
     * @return JsonResponse
     *
     * @throws AuthorizationException
     * @OA\Delete(
     *     path="/api/orders/{orderId}/permanently",
     *     tags={"Orders"},
     *     summary="Delete order permanently",
     *     operationId="Delete order permanently",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Response(response=204, description="Successful operation"),
     * )
     */
    public function deletePermanently(int $orderId): JsonResponse
    {
        $this->authorize('orders delete-permanently');

        if (!($order = Order::withTrashed()->find($orderId))) {
            return $this->makeErrorResponse(null, Response::HTTP_NOT_FOUND);
        }

        try {
            $this->orderService->deleteOrderPermanently($order);
            return $this->makeSuccessResponse(null, Response::HTTP_NO_CONTENT);
        } catch (Exception $e) {
            Log::error($e->getTraceAsString());

            return $this->makeErrorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Mark order as reviewed
     *
     * @param Order $order
     * @return OrderResource|JsonResponse
     *
     * @OA\Put(
     *     path="/api/orders/{orderId}/mark-reviewed",
     *     tags={"Orders"},
     *     summary="Mark order as reviewed",
     *     operationId="Mark order as reviewed",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/OrderResource")
     *     ),
     * )
     *
     * @throws AuthorizationException
     * @throws Throwable
     */
    public function markReviewed(Order $order)
    {
        $this->authorize('orderReview', Order::class);

        try {
            return OrderResource::make(
                $this->orderService->markReviewed($order)->loadMissingRelations()
            );
        } catch (Exception $e) {
            Log::error($e->getTraceAsString());
            return $this->makeErrorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Add single order vehicle
     *
     * @param Order $order
     * @param SingleVehicleRequest $request
     * @return JsonResponse|OrderResource
     *
     * @throws AuthorizationException
     * @throws Throwable
     * @OA\Post(
     *     path="/api/orders/{orderId}/vehicles",
     *     tags={"Orders"},
     *     summary="Add single vehicle to order",
     *     operationId="Add vehicle",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(
     *          name="inop",
     *          in="query",
     *          description="inop",
     *          required=false,
     *          @OA\Schema(
     *              type="boolean",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="enclosed",
     *          in="query",
     *          description="enclosed",
     *          required=false,
     *          @OA\Schema(
     *              type="boolean",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="vin",
     *          in="query",
     *          description="vin",
     *          required=false,
     *          @OA\Schema(
     *              type="string",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="year",
     *          in="query",
     *          description="year",
     *          required=false,
     *          @OA\Schema(
     *              type="number",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="make",
     *          in="query",
     *          description="make",
     *          required=false,
     *          @OA\Schema(
     *              type="string",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="model",
     *          in="query",
     *          description="model",
     *          required=false,
     *          @OA\Schema(
     *              type="string",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="type_id",
     *          in="query",
     *          description="type id",
     *          required=false,
     *          @OA\Schema(
     *              type="integer",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="color",
     *          in="query",
     *          description="color",
     *          required=false,
     *          @OA\Schema(
     *              type="string",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="license_plate",
     *          in="query",
     *          description="license_plate",
     *          required=false,
     *          @OA\Schema(
     *              type="string",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="odometer",
     *          in="query",
     *          description="odometer",
     *          required=false,
     *          @OA\Schema(
     *              type="number",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="price",
     *          in="query",
     *          description="price",
     *          required=false,
     *          @OA\Schema(
     *              type="number",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="stock_number",
     *          in="query",
     *          description="stock number",
     *          required=false,
     *          @OA\Schema(
     *              type="string",
     *          )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/OrderResource")
     *     ),
     * )
     */
    public function addVehicle(Order $order, SingleVehicleRequest $request)
    {
        $this->authorize('update', $order);

        try {
            return OrderResource::make(
                $this->orderService->addVehicle(
                    $order,
                    $request->validated()
                )->loadMissingRelations()
            );
        } catch (Exception $e) {
            Log::error($e->getTraceAsString());
            return $this->makeErrorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Edit single order vehicle
     *
     * @param Order $order
     * @param Vehicle $vehicle
     * @param SingleVehicleRequest $request
     * @return JsonResponse|OrderResource
     *
     * @throws AuthorizationException
     * @throws Throwable
     * @OA\Put(
     *     path="/api/orders/{orderId}/vehicles/{vehicleId}",
     *     tags={"Orders"},
     *     summary="Edit single vehicle in order",
     *     operationId="Edit vehicle",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(
     *          name="inop",
     *          in="query",
     *          description="inop",
     *          required=false,
     *          @OA\Schema(
     *              type="boolean",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="enclosed",
     *          in="query",
     *          description="enclosed",
     *          required=false,
     *          @OA\Schema(
     *              type="boolean",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="vin",
     *          in="query",
     *          description="vin",
     *          required=false,
     *          @OA\Schema(
     *              type="string",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="year",
     *          in="query",
     *          description="year",
     *          required=false,
     *          @OA\Schema(
     *              type="number",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="make",
     *          in="query",
     *          description="make",
     *          required=false,
     *          @OA\Schema(
     *              type="string",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="model",
     *          in="query",
     *          description="model",
     *          required=false,
     *          @OA\Schema(
     *              type="string",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="type_id",
     *          in="query",
     *          description="type id",
     *          required=false,
     *          @OA\Schema(
     *              type="integer",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="color",
     *          in="query",
     *          description="color",
     *          required=false,
     *          @OA\Schema(
     *              type="string",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="license_plate",
     *          in="query",
     *          description="license_plate",
     *          required=false,
     *          @OA\Schema(
     *              type="string",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="odometer",
     *          in="query",
     *          description="odometer",
     *          required=false,
     *          @OA\Schema(
     *              type="number",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="price",
     *          in="query",
     *          description="price",
     *          required=false,
     *          @OA\Schema(
     *              type="number",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="stock_number",
     *          in="query",
     *          description="stock number",
     *          required=false,
     *          @OA\Schema(
     *              type="string",
     *          )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/OrderResource")
     *     ),
     * )
     */
    public function editVehicle(Order $order, Vehicle $vehicle, SingleVehicleRequest $request)
    {
        $this->authorize('update', $order);

        if ($vehicle->order_id !== $order->id) {
            return $this->makeErrorResponse('Vehicle not found.', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            return OrderResource::make(
                $this->orderService->editVehicle(
                    $order,
                    $vehicle,
                    $request->validated()
                )->loadMissingRelations()
            );
        } catch (Exception $e) {
            Log::error($e->getTraceAsString());
            return $this->makeErrorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Delete single order vehicle
     *
     * @param DeleteVehicleRequest $request
     * @param Order $order
     * @param Vehicle $vehicle
     * @return JsonResponse
     *
     * @OA\Delete(
     *     path="/api/orders/{orderId}/vehicles/{vehicleId}",
     *     tags={"Orders"},
     *     summary="Delete vehicle from order",
     *     operationId="Delete vehicle",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Response(
     *         response=204,
     *         description="Successful operation",
     *     ),
     * )
     *
     */
    public function deleteVehicle(DeleteVehicleRequest $request, Order $order, Vehicle $vehicle): JsonResponse
    {
        try {
            $this->orderService->deleteVehicle($order, $vehicle);
            return $this->makeSuccessResponse(null, 204);
        } catch (Exception $e) {
            return $this->makeErrorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Add single order expense
     *
     * @param Order $order
     * @param SingleExpenseRequest $request
     * @return JsonResponse|OrderResource
     *
     * @OA\Post(
     *     path="/api/orders/{orderId}/expenses",
     *     tags={"Orders"},
     *     summary="Add single expense to order",
     *     operationId="Add expense",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(
     *          name="type_id",
     *          in="query",
     *          description="expense type id",
     *          required=false,
     *          @OA\Schema(
     *              type="integer",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="price",
     *          in="query",
     *          description="price",
     *          required=false,
     *          @OA\Schema(
     *              type="number",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="date",
     *          in="query",
     *          description="exense date",
     *          required=false,
     *          @OA\Schema(
     *              type="date",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="receipt_file",
     *          in="query",
     *          description="receipt photo",
     *          required=false,
     *          @OA\Schema(
     *              type="file",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="to",
     *          in="query",
     *          description="User invoice to",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *              enum={"broker", "customer"}
     *          )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/OrderResource")
     *     ),
     * )
     *
     * @throws AuthorizationException
     * @throws Throwable
     */
    public function addExpense(Order $order, SingleExpenseRequest $request)
    {
        $this->authorize('update', $order);

        try {
            return OrderResource::make(
                $this->orderService->addExpense(
                    $order,
                    $request->validated()
                )->loadMissingRelations()
            );
        } catch (Exception $e) {
            Log::error($e->getTraceAsString());
            return $this->makeErrorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Edit single order expense
     *
     * @param Order $order
     * @param Expense $expense
     * @param SingleExpenseRequest $request
     * @return JsonResponse|OrderResource
     *
     * @OA\Post(
     *     path="/api/orders/{orderId}/expenses/{expense}",
     *     tags={"Orders"},
     *     summary="Edit single order expense",
     *     operationId="Edit expense",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="type_id", in="query", description="expense type", required=false,
     *          @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(name="price", in="query", description="price", required=false,
     *          @OA\Schema(type="number")
     *     ),
     *     @OA\Parameter(name="date", in="query", description="exense date", required=false,
     *          @OA\Schema(type="date")
     *     ),
     *     @OA\Parameter(name="receipt_file", in="query", description="receipt photo", required=false,
     *          @OA\Schema(type="file")
     *     ),
     *     @OA\Parameter(
     *          name="to",
     *          in="query",
     *          description="User invoice to",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *              enum={"broker", "customer"}
     *          )
     *     ),
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/OrderResource")
     *     ),
     * )
     *
     * @throws AuthorizationException
     */
    public function editExpense(Order $order, Expense $expense, SingleExpenseRequest $request)
    {
        $this->authorize('update', $order);

        if ($expense->order_id !== $order->id) {
            return $this->makeErrorResponse('Expense not found.', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            return OrderResource::make(
                $this->orderService->editExpense(
                    $order,
                    $expense,
                    $request->validated()
                )->loadMissingRelations()
            );
        } catch (Exception $e) {
            Log::error($e->getTraceAsString());
            return $this->makeErrorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Delete single order expense
     *
     * @param Order $order
     * @param Expense $expense
     * @return JsonResponse
     *
     * @OA\Delete(
     *     path="/api/orders/{orderId}/expenses/{expenseId}",
     *     tags={"Orders"},
     *     summary="Delete expense from order",
     *     operationId="Delete expense",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Response(response=204, description="Successful operation"),
     * )
     *
     * @throws AuthorizationException
     */
    public function deleteExpense(Order $order, Expense $expense): JsonResponse
    {
        $this->authorize('update', $order);

        if ($expense->order_id !== $order->id) {
            return $this->makeErrorResponse('Expense not found.', 422);
        }

        try {
            $this->orderService->deleteExpense($order, $expense);
            return $this->makeSuccessResponse(null, 204);
        } catch (Exception $e) {
            Log::error($e->getTraceAsString());
            return $this->makeErrorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Delete single order bonus
     *
     * @param Order $order
     * @param Bonus $bonus
     * @return JsonResponse
     *
     * @throws AuthorizationException
     * @OA\Delete(
     *     path="/api/orders/{orderId}/bonuses/{bonusId}",
     *     tags={"Orders"},
     *     summary="Delete bonus from order",
     *     operationId="Delete bonus",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Response(response=204, description="Successful operation"),
     * )
     *
     */
    public function deleteBonus(Order $order, Bonus $bonus): JsonResponse
    {
        $this->authorize('update', $order);

        if ($bonus->order_id !== $order->id) {
            return $this->makeErrorResponse('Bonus not found.', 422);
        }

        try {
            $this->orderService->deleteBonus($order, $bonus);
            return $this->makeSuccessResponse(null, 204);
        } catch (Exception $e) {
            Log::error($e->getTraceAsString());
            return $this->makeErrorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Add single order attachment
     *
     * @param SingleAttachmentRequest $request
     * @param Order $order
     * @return JsonResponse|OrderResource
     *
     * @OA\Post(
     *     path="/api/orders/{orderId}/attachments",
     *     tags={"Orders"},
     *     summary="Add single attachment to order",
     *     operationId="Add attachment",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="attachment", in="query", description="attachment file", required=false,
     *          @OA\Schema(type="file",)
     *     ),
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/OrderResource")
     *     ),
     * )
     *
     * @throws AuthorizationException
     */
    public function addAttachment(SingleAttachmentRequest $request, Order $order)
    {
        $this->authorize('orders add-attachment');

        try {
            return OrderResource::make(
                $this->orderService->addAttachment(
                    $order,
                    $request->attachment
                )->loadMissingRelations()
            );
        } catch (Exception $e) {
            Log::error($e->getTraceAsString());
            return $this->makeErrorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Delete single order attachment
     *
     * @param Order $order
     * @param int $id
     * @return JsonResponse
     *
     * @OA\Delete(
     *     path="/api/orders/{orderId}/attachments/{attachmentId}",
     *     tags={"Orders"},
     *     summary="Delete attachment from order",
     *     operationId="Delete attachment",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Response(response=204, description="Successful operation"),
     * )
     *
     * @throws AuthorizationException
     */
    public function deleteAttachment(Order $order, int $id): JsonResponse
    {
        $this->authorize('orders add-attachment');

        try {
            $this->orderService->deleteAttachment($order, $id);

            return $this->makeSuccessResponse(null, 204);
        } catch (Exception $e) {
            Log::error($e->getTraceAsString());
            return $this->makeErrorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Delete driver uploaded document
     *
     * @param Order $order
     * @param int $id
     *
     * @OA\Delete(
     *     path="/api/orders/{orderId}/driver-documents/{driverDocumentId}",
     *     tags={"Orders"},
     *     summary="Delete driver uploaded document",
     *     operationId="Delete document",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Response(
     *         response=204,
     *         description="Successful operation",
     *     ),
     * )
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function deleteDocument(Order $order, int $id): JsonResponse
    {
        $this->authorize('orders add-attachment');

        try {
            $this->orderService->deleteDocument(
                $order,
                $id
            );
            return $this->makeSuccessResponse(null, Response::HTTP_NO_CONTENT);
        } catch (Exception $e) {
            return $this->makeErrorResponse(null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Delete driver uploaded photo
     *
     * @param Order $order
     * @param int $id
     *
     * @OA\Delete(
     *     path="/api/orders/{orderId}/driver-photos/{driverPhotoId}",
     *     tags={"Orders"},
     *     summary="Delete driver uploaded photo",
     *     operationId="Delete photo",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Response(
     *         response=204,
     *         description="Successful operation",
     *     ),
     * )
     *
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function deletePhoto(Order $order, int $id): JsonResponse
    {
        $this->authorize('orders add-attachment');

        try {
            $this->orderService->deletePhoto(
                $order,
                $id
            );
            return $this->makeSuccessResponse(null, 204);
        } catch (Exception $e) {
            Log::error($e->getTraceAsString());
            return $this->makeErrorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Assign driver to order
     *
     * @param AssignDriverRequest $request
     * @param Order $order
     * @return JsonResponse|OrderResource
     *
     * @OA\Put(
     *     path="/api/orders/{orderId}/assign-driver",
     *     tags={"Orders"},
     *     summary="Assign driver to order",
     *     operationId="Assign driver to order",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="dispatcher_id", in="query", required=true,
     *          @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(name="driver_id", in="query", required=true,
     *          @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/OrderResource")
     *     ),
     * )
     *
     * @throws AuthorizationException
     * @throws Throwable
     */
    public function assignDriver(AssignDriverRequest $request, Order $order)
    {
        $this->authorize('create', $order);

        try {
            return OrderResource::make(
                $this->orderService->assignDriver(
                    $order,
                    $request->validated()
                )
            );
        } catch (Exception $e) {
            Log::error($e->getTraceAsString());
            return $this->makeErrorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Duplicate order entity
     *
     * @param Order $order
     * @return OrderResource|JsonResponse
     *
     * @OA\Get(
     *     path="/api/orders/{orderId}/duplicate",
     *     tags={"Orders"},
     *     summary="Duplicate order entity",
     *     operationId="Duplicate order entity",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/OrderResource")
     *     ),
     * )
     *
     * @throws AuthorizationException
     */
    public function duplicateOrder(Order $order)
    {
        $this->authorize('create', Order::class);

        if (!($order->isStatusAssigned() || $order->isStatusNew())) {
            return $this->makeErrorResponse(
                trans('This order can not be duplicated.'),
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        try {
            return OrderResource::make(
                $this->orderService->duplicateOrder($order)
            );
        } catch (Throwable $e) {
            Log::error($e->getTraceAsString());
            return $this->makeErrorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Split order
     *
     * @param SplitOrderRequest $splitRequest
     * @param Order $order
     * @return JsonResponse
     *
     * @OA\Get(
     *     path="/api/orders/{orderId}/split",
     *     tags={"Orders"},
     *     summary="Split order entity",
     *     operationId="Split order entity",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(
     *          name="source_load_id",
     *          in="query",
     *          description="Original order load id",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="destination",
     *          in="query",
     *          description="Array of new orders data",
     *          required=true,
     *          @OA\Schema(
     *              type="array",
     *              @OA\Items(
     *                  type="object",
     *                  allOf={
     *                      @OA\Schema(
     *                          @OA\Property(property="load_id", type="string", description="New order load id"),
     *                          @OA\Property(
     *                              property="vehicles",
     *                              type="array",
     *                              description="Array of vehicle id's to be moved to the new order",
     *                              @OA\Items(type="integer")
     *                          ),
     *                      )
     *                  }
     *              ),
     *          )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *     ),
     * )
     *
     * @throws AuthorizationException
     * @throws Throwable
     */
    public function splitOrder(SplitOrderRequest $splitRequest, Order $order): JsonResponse
    {
        $this->authorize('create', Order::class);

        try {
            $this->orderService->splitOrder(
                $order,
                $this->user(),
                $splitRequest->validated()
            );
            return $this->makeSuccessResponse(null, 200);
        } catch (Exception $e) {
            Log::error($e->getTraceAsString());
            return $this->makeErrorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get Invoice pdf file
     *
     * @param Order $order
     * @param DownloadInvoiceRequest $request
     * @param GeneratePdfService $generatePdfService
     * @return JsonResponse|StreamedResponse
     *
     * @OA\Get(
     *     path="/api/orders/{orderId}/get-invoice/{recipient}",
     *     tags={"Orders"},
     *     summary="Get Invoice pdf file",
     *     operationId="Get Invoice pdf file",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter (
     *          name="invoice",
     *          in="path",
     *          description="Invoice recipient. For mobile - only customer",
     *          required=true,
     *          @OA\Schema (
     *              type="string",
     *              enum={"broker","customer"}
     *          )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *     ),
     * )
     */
    public function getInvoice(Order $order, string $recipient, GeneratePdfService $generatePdfService)
    {
        try {
            return response()->streamDownload(function () use ($order, $recipient, $generatePdfService) {
                try {
                    $generatePdfService->getInvoice($order, ['recipient' => $recipient]);
                } catch (EmptyInvoiceTotalDue $e) {
                    throw new AccessDeniedHttpException($e->getMessage());
                }
            });
        } catch (Exception $e) {
            Log::error($e->getTraceAsString());
            return $this->makeErrorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get BOL pdf file
     *
     * @param Order $order
     * @param GeneratePdfService $generatePdfService
     * @return JsonResponse|StreamedResponse
     *
     * @throws AuthorizationException
     * @throws Throwable
     * @OA\Get(
     *     path="/api/orders/{orderId}/get-bol",
     *     tags={"Orders"},
     *     summary="Get BOL pdf file",
     *     operationId="Get BOL pdf file",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *     ),
     * )
     *
     */
    public function getBol(Order $order, GeneratePdfService $generatePdfService)
    {
        $this->authorize('orders read');

        try {
            return response()->streamDownload(
                function () use ($order, $generatePdfService) {
                    $generatePdfService->getBol($order);
                }
            );
        } catch (Exception $e) {
            Log::error($e->getTraceAsString());
            return $this->makeErrorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Send Invoice pdf file
     *
     * @param SendDocsRequest $request
     * @param GeneratePdfService $generatePdfService
     * @return JsonResponse
     *
     * @OA\Post(
     *     path="/api/orders/send-docs",
     *     tags={"Orders"},
     *     summary="Send Invoice/BOL/W9 pdf files",
     *     operationId="Send Invoice/BOL/W9 files",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\RequestBody(
     *          @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema (
     *                  type="object",
     *                  required={"send_via", "content", "orders"},
     *                  @OA\Property (
     *                      property="recipient_email",
     *                      description="Array of recipient emails (required if 'send_via' contains 'email')",
     *                      type="array",
     *                      nullable=true,
     *                      @OA\Items(
     *                          type="string",
     *                          example="my.email@gmail.com"
     *                      )
     *                  ),
     *                  @OA\Property (
     *                      property="recipient_fax",
     *                      description="Recipient fax (required if 'send_via' contains 'fax')",
     *                      type="string",
     *                      nullable=true,
     *                      example="+123456789012"
     *                  ),
     *                  @OA\Property (
     *                      property="send_via",
     *                      description="Selected sending channels",
     *                      type="array",
     *                      nullable=false,
     *                      @OA\Items (type="string", enum={"email", "fax"})
     *                  ),
     *                  @OA\Property (
     *                      property="content",
     *                      description="Selected sending docs",
     *                      type="array",
     *                      nullable=false,
     *                      @OA\Items (type="string", enum={"invoice", "bol", "w9"})
     *                  ),
     *                  @OA\Property (
     *                      property="orders",
     *                      description="Selected orders for sending docs",
     *                      type="array",
     *                      nullable=false,
     *                      @OA\Items (
     *                          allOf={
     *                              @OA\Schema (
     *                                  type="object",
     *                                  required={"id"},
     *                                  @OA\Property (
     *                                      property="id",
     *                                      type="integer",
     *                                      nullable=false,
     *                                      description="Order id"
     *                                  ),
     *                                  @OA\Property (
     *                                      property="invoice_id",
     *                                      type="string",
     *                                      nullable=true,
     *                                      description="Invoice Id (required if 'content' contains 'invoice')"
     *                                  ),
     *                                  @OA\Property (
     *                                      property="invoice_date",
     *                                      type="string",
     *                                      nullable=true,
     *                                      description="Invoice date (required if 'content' contains 'invoice'). Format m/d/Y",
     *                                      example="12/21/2021"
     *                                  ),
     *                                  @OA\Property (
     *                                      property="show_shipper_info",
     *                                      description="",
     *                                      type="boolean",
     *                                      nullable=true
     *                                  ),
     *                                  @OA\Property (
     *                                      property="invoice_recipient",
     *                                      type="string",
     *                                      nullable=true,
     *                                      description="Invoice recipient (required if 'content' contains 'invoice')",
     *                                      enum={"broker", "customer"}
     *                                  ),
     *                              )
     *                          }
     *                      )
     *                  )
     *             )
     *          )
     *     ),
     *     @OA\Response(response=200, description="Successful operation"),
     * )
     * @throws Throwable
     */
    public function sendDocs(SendDocsRequest $request, GeneratePdfService $generatePdfService): JsonResponse
    {
        try {
            $generatePdfService->sendDocs($request->user(), $request->dto());

            return $this->makeSuccessResponse(null, Response::HTTP_OK);
        } catch (EmptyInvoiceTotalDue | SenderDoesNotHaveEmail $e) {
            return $this->makeErrorResponse($e->getMessage(), Response::HTTP_FAILED_DEPENDENCY);
        } catch (Exception $e) {
            Log::error($e->getTraceAsString());
            return $this->makeErrorResponse(null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get order pdf file
     *
     * @param Order $order
     * @param GeneratePdfService $generatePdfService
     * @return JsonResponse|StreamedResponse
     *
     * @throws AuthorizationException
     * @OA\Get(
     *     path="/api/orders/{orderId}/print",
     *     tags={"Orders"},
     *     summary="Get order pdf file for printing",
     *     operationId="Get order pdf file for printing",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Response(response=200, description="Successful operation"),
     * )
     *
     */
    public function printOrder(Order $order, GeneratePdfService $generatePdfService)
    {
        $this->authorize('orders read');

        try {
            return response()->streamDownload(
                function () use ($generatePdfService, $order) {
                    $generatePdfService->printOrder($order);
                }
            );
        } catch (Exception $e) {
            Log::error($e->getTraceAsString());

            return $this->makeErrorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param SignPublicBolRequest $request
     * @param string $token
     * @return JsonResponse
     * @throws Throwable
     *
     * @OA\Post(
     *     path="/api/orders/public-bol/{token}",
     *     tags={"Orders"},
     *     summary="Sign pickup/delivery inspection",
     *     operationId="Sign pickup/delivery inspection",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter (
     *          name="token",
     *          in="path",
     *          required=true,
     *          description="Public token",
     *          @OA\Schema (
     *              type="string"
     *          )
     *     ),
     *     @OA\Parameter (
     *          name="first_name",
     *          in="query",
     *          required=true,
     *          description="Customer first name (signer)",
     *          @OA\Schema (
     *              type="string"
     *          )
     *     ),
     *     @OA\Parameter (
     *          name="last_name",
     *          in="query",
     *          required=true,
     *          description="Customer last name (signer)",
     *          @OA\Schema (
     *              type="string"
     *          )
     *     ),
     *     @OA\Parameter (
     *          name="signed_time",
     *          in="query",
     *          required=true,
     *          description="Sign time",
     *          @OA\Schema (
     *              type="string",
     *              example="m/d/Y g:i A"
     *          )
     *     ),
     *     @OA\Parameter (
     *          name="inspection_agree",
     *          in="query",
     *          required=true,
     *          description="Customer has to agree with inspection data",
     *          @OA\Schema (
     *              type="boolean"
     *          )
     *     ),
     *     @OA\Parameter (
     *          name="sign_file",
     *          in="query",
     *          required=true,
     *          description="Signature file",
     *          @OA\Schema (
     *              type="string",
     *              format="binary"
     *          )
     *     ),
     *     @OA\Response(response=200, description="Successful operation"),
     * )
     */
    public function signPublicBol(SignPublicBolRequest $request, string $token): JsonResponse
    {
        try {
            $this->orderService->signPublicBol($token, $request->validated());
            return $this->makeSuccessResponse();
        } catch (OrderSignatureLinkExpired | HaveToAgreeWithInspection $e) {
            return $this->makeErrorResponse($e->getMessage(), Response::HTTP_FAILED_DEPENDENCY);
        } catch (Exception $e) {
            return $this->makeErrorResponse(null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Public BOL
     *
     * @param string $token
     * @return OrderBolResource|JsonResponse
     *
     * @OA\Get(
     *     path="/api/orders/public-bol/{token}",
     *     tags={"Orders"},
     *     summary="Public BOL",
     *     operationId="Public BOL",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/OrderBolResource")
     *     ),
     * )
     */
    public function publicBol(string $token)
    {
        try {
            return OrderBolResource::make($this->orderService->publicBol($token));
        } catch (ModelNotFoundException $e) {
            return $this->makeErrorResponse(null, Response::HTTP_NOT_FOUND);
        } catch (Exception $e) {
            Log::error($e->getTraceAsString());
            return $this->makeErrorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Public BOL vehicle photos
     *
     * @param string $token
     * @param int $vehicleId
     * @param OrderPhotoService $orderPhotoService
     * @return JsonResponse|StreamedResponse
     *
     * @OA\Get(
     *     path="/api/orders/public-bol/{token}/vehicle/{vehicleId}/photos",
     *     tags={"Orders"},
     *     summary="Public BOL vehicle photos",
     *     operationId="Public BOL vehicle photos",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Response(response=200, description="Successful operation"),
     * )
     */
    public function publicVehiclePhotos(string $token, int $vehicleId, OrderPhotoService $orderPhotoService)
    {
        try {
            return $orderPhotoService->vehiclePhotos($token, $vehicleId);
        } catch (Exception $e) {
            Log::error($e->getTraceAsString());
            return $this->makeErrorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Public BOL order photos
     *
     * @param string $token
     * @param OrderPhotoService $orderPhotoService
     * @return JsonResponse|StreamedResponse
     *
     * @OA\Get(
     *     path="/api/orders/public-bol/{token}/photos",
     *     tags={"Orders"},
     *     summary="Public BOL order photos",
     *     operationId="Public BOL order photos",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Response(response=200, description="Successful operation"),
     * )
     */
    public function publicOrderPhotos(string $token, OrderPhotoService $orderPhotoService)
    {
        try {
            return $orderPhotoService->orderPhotos($token);
        } catch (Exception $e) {
            Log::error($e->getTraceAsString());
            return $this->makeErrorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Public BOL pdf
     *
     * @param string $token
     * @param Request $request
     * @return JsonResponse|StreamedResponse
     *
     * @throws Throwable
     * @OA\Get(
     *     path="/api/orders/public-pdf/{token}/bol.pdf",
     *     tags={"Orders"},
     *     summary="Public BOL pdf",
     *     operationId="Public BOL pdf",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Response(response=200, description="Successful operation",),
     * )
     */
    public function publicPdfBol(string $token, Request $request)
    {
        try {
            return response()->streamDownload(
                function () use ($token, $request) {
                    $this->orderService->publicPdfBol(
                        $token,
                        $request->query('show_shipper_info', false)
                    );
                },
                'bol.pdf',
            );
        } catch (Exception $e) {
            Log::error($e->getTraceAsString());

            return $this->makeErrorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Public Invoice pdf
     *
     * @param PublicInvoiceRequest $request
     * @param string $token
     * @return JsonResponse|StreamedResponse
     * @OA\Get(
     *     path="/api/orders/public-pdf/{token}/{recipient}invoice.pdf",
     *     tags={"Orders"},
     *     summary="Public Invoice pdf",
     *     operationId="Public Invoice pdf",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter (
     *          name="token",
     *          in="path",
     *          description="Public access token",
     *          required=true,
     *          @OA\Schema (type="string")
     *     ),
     *     @OA\Parameter (
     *          name="recipient",
     *          in="path",
     *          description="Invoice recipient",
     *          required=true,
     *          @OA\Schema (type="string", enum={"broker", "customer"})
     *     ),
     *     @OA\Parameter (
     *          name="invoice_id",
     *          in="query",
     *          description="Invoice id",
     *          required=false,
     *          @OA\Schema (type="string", nullable=true)
     *     ),
     *     @OA\Parameter (
     *          name="invoice_date",
     *          in="query",
     *          description="Invoice date (m/d/Y)",
     *          required=false,
     *          @OA\Schema (type="string", nullable=true, example="12/21/21")
     *     ),
     *     @OA\Response(response=200, description="Successful operation",),
     * )
     */
    public function publicPdfInvoice(PublicInvoiceRequest $request, string $token)
    {
        $invoice = $request->dto();
        try {
            return response()->streamDownload(function () use ($token, $invoice) {
                try {
                    $this->orderService->publicPdfInvoice($token, $invoice);
                } catch (EmptyInvoiceTotalDue $e) {
                    throw new AccessDeniedHttpException($e->getMessage());
                }
            });
        } catch (Exception $e) {
            Log::error($e->getTraceAsString());
            return $this->makeErrorResponse(null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get order history
     *
     * @param int $orderId
     * @return AnonymousResourceCollection|JsonResponse
     *
     * @throws AuthorizationException
     * @OA\Get(
     *     path="/api/orders/{orderId}/history",
     *     tags={"Orders"},
     *     summary="Get order history",
     *     operationId="Get order history",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/HistoryListResource")
     *     ),
     * )
     */
    public function orderHistory(int $orderId)
    {
        $this->authorize('orders read');

        try {
            if (
                Order::query()
                    ->where(
                        [
                            Order::TABLE_NAME . '.id' => $orderId,
                            'carrier_id' => Auth::user()->getCompany()->id
                        ]
                    )
                    ->doesntExist()
            ) {
                return $this->makeErrorResponse(null, Response::HTTP_NOT_FOUND);
            }

            return HistoryListResource::collection(
                $this->orderService->orderHistory($orderId)
            );
        } catch (Exception $e) {
            Log::error($e->getTraceAsString());
            return $this->makeErrorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get order history detailed paginate
     *
     * @param int $orderId
     * @param OrderHistoryRequest $request
     * @return AnonymousResourceCollection|JsonResponse
     *
     * @throws AuthorizationException
     * @OA\Get(
     *     path="/api/orders/{orderId}/history-detailed",
     *     tags={"Orders"},
     *     summary="Get order history detailed",
     *     operationId="Get order history detailed",
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
     *         @OA\JsonContent(ref="#/components/schemas/HistoryPaginatedResource")
     *     ),
     * )
     */
    public function orderHistoryDetailed(int $orderId, OrderHistoryRequest $request)
    {
        $this->authorize('orders read');

        try {
            if (
                Order::query()
                    ->where(
                        [
                            Order::TABLE_NAME . '.id' => $orderId,
                            'carrier_id' => Auth::user()->getCompany()->id
                        ]
                    )
                    ->doesntExist()
            ) {
                return $this->makeErrorResponse(null, Response::HTTP_NOT_FOUND);
            }

            $filterFields = $request->validated();

            $perPage = (int) $request->input('per_page', 10);

            $history = History::query()
                ->whereHas(
                    'user.roles',
                    function (Builder $builder) {
                        $builder->whereNotIn('name',  User::DRIVER_ROLES);
                    }
                )
                ->where(
                    [
                        ['model_id', $orderId],
                        ['model_type', Order::class],
                    ]
                )
                ->whereType(History::TYPE_CHANGES)
                ->filter($filterFields)
                ->latest('id')
                ->paginate($perPage);

            if ($history) {
                foreach ($history as &$h) {
                    if (isset($h['meta']) && is_array($h['meta'])) {
                        $h['message'] = trans($h['message'], $h['meta']);
                    }
                }
            }

            return HistoryPaginatedResource::collection($history);
        } catch (Exception $e) {
            Log::error($e->getTraceAsString());
            return $this->makeErrorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get order history detailed paginate
     *
     * @param int $orderId
     * @return AnonymousResourceCollection
     *
     * @throws AuthorizationException
     * @OA\Get(
     *     path="/api/orders/{orderId}/history-users-list",
     *     tags={"Orders"},
     *     summary="Get list users changes order",
     *     operationId="Get list users changes order",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/DriversListResource")
     *     ),
     * )
     */
    public function orderHistoryUsers(int $orderId): AnonymousResourceCollection
    {
        $this->authorize('orders read');

        $users = User::active()
            ->whereHas(
                'roles',
                function (Builder $builder) {
                    $builder->whereNotIn('name', User::DRIVER_ROLES);
                }
            )
            ->whereIn(
                'id',
                History::query()
                    ->select('user_id')
                    ->where(
                        [
                            ['model_id', $orderId],
                            ['model_type', Order::class],
                        ]
                    )
                    ->whereType(History::TYPE_CHANGES)
                    ->getQuery()
            )
            ->orderByRaw(
                "
                CASE
                    WHEN owner_id = '" . Auth::user()->id . "' THEN 1
                    ELSE 0
                END DESC,
                concat(first_name, ' ', last_name) ASC
            "
            );

        return DriversListResource::collection($users->get());
    }

    /**
     * Get order from offers
     *
     * @param Order $order
     * @param TakeOfferRequest $request
     * @return JsonResponse|OrderResource
     *
     * @OA\Put(
     *     path="/api/orders/{orderId}/take",
     *     tags={"Orders"},
     *     summary="Get order from offers",
     *     operationId="Get order from offers",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="driver_id", in="query", description="Driver id", required=false,
     *          @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/OrderResource")
     *     ),
     * )
     *
     * @throws AuthorizationException
     * @throws Throwable
     */
    public function take(Order $order, TakeOfferRequest $request)
    {
        $this->authorize('orders take-offer');

        try {
            return OrderResource::make(
                $this->orderService->takeOrder($order, $request->driver_id)
            );
        } catch (Exception $e) {
            return $this->makeErrorResponse(null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Send order to offers
     *
     * @param Order $order
     * @return JsonResponse|OrderResource
     *
     * @OA\Put(
     *     path="/api/orders/{orderId}/release",
     *     tags={"Orders"},
     *     summary="Send order to offers",
     *     operationId="Send order to offers",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/OrderResource")
     *     ),
     * )
     *
     * @throws AuthorizationException
     * @throws Throwable
     */
    public function release(Order $order)
    {
        $this->authorize('orders release-offer');

        try {
            return OrderResource::make($this->orderService->releaseOrder($order));
        } catch (OrderCantBeMovedOffers $e) {
            return $this->makeErrorResponse($e->getMessage(), Response::HTTP_NOT_ACCEPTABLE);
        } catch (Exception $e) {
            return $this->makeErrorResponse(null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Manually change order status
     *
     * @param Order $order
     * @param ChangeOrderStatusRequest $request
     * @return OrderResource|JsonResponse
     * @throws AuthorizationException|Throwable
     *
     * @OA\Put(
     *     path="/api/orders/{orderId}/change-status",
     *     tags={"Orders"},
     *     summary="Manually change order status",
     *     operationId="Manually change order status",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="status", in="query", description="Order status", required=false,
     *          @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(name="pickup_date_actual", in="query", description="", required=false,
     *          @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(name="delivery_date_actual", in="query", description="", required=false,
     *          @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/OrderResource")
     *     ),
     * )
     */
    public function changeStatus(Order $order, ChangeOrderStatusRequest $request)
    {
        $this->authorize('changeStatus', $order);

        try {
            return OrderResource::make(
                $this->statusService()->changeStatus(
                    $order,
                    $request->input('status'),
                    $request->validated()
                )
            );
        } catch (ChangeOrderStatusException $e) {
            return $this->makeErrorResponse($e->getMessage(), Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (Exception $e) {
            return $this->makeErrorResponse(null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param ChangeDeductFlagRequest $request
     * @param Order $order
     * @return JsonResponse|OrderResource
     *
     * @OA\Put(
     *     path="/api/orders/{orderId}/change-deduct-from-driver",
     *     tags={"Driver reports"},
     *     summary="Add/remove flag deduct from driver",
     *     operationId="Add/remove flag deduct from driver",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="deducted_note", in="query", description="Note for deducted flag", required=false,
     *          @OA\Schema(type="number",)
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/OrderResource")
     *     ),
     * )
     */
    public function changeDeductFromDriver(ChangeDeductFlagRequest $request, Order $order)
    {
        $order = $this->orderService->changeDeductFromDriver($order, $request->validated());

        if ($order !== null) {
            return OrderResource::make($order);
        }

        return $this->makeErrorResponse(null, Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    /**
     * Add payment stage
     *
     * @param Order $order
     * @param PaymentStageRequest $request
     * @return JsonResponse|PaymentStageResource
     * @throws AuthorizationException
     *
     * @OA\Post(
     *     path="/api/orders/{orderId}/payment-stage",
     *     tags={"Orders"},
     *     summary="Add payment stage",
     *     operationId="Add payment stage",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="amount", in="query", description="", required=true,
     *          @OA\Schema(type="number",)
     *     ),
     *     @OA\Parameter(name="payment_date", in="query", description="", required=true,
     *          @OA\Schema(type="string",)
     *     ),
     *     @OA\Parameter(name="payer", in="query", description="", required=true,
     *          @OA\Schema(type="string", enum ={"customer","broker","carrier"})
     *     ),
     *     @OA\Parameter(name="method_id", in="query", description="", required=true,
     *          @OA\Schema(type="integer",)
     *     ),
     *     @OA\Parameter(name="uship_number", in="query", description="", required=false,
     *          @OA\Schema(type="string",)
     *     ),
     *     @OA\Parameter(name="reference_number", in="query", description="", required=false,
     *          @OA\Schema(type="string",)
     *     ),
     *     @OA\Parameter(name="notes", in="query", description="", required=false,
     *          @OA\Schema(type="string",)
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Successful operation",
     *     ),
     * )
     *
     */
    public function addPaymentStage(Order $order, PaymentStageRequest $request)
    {
        $this->authorize('orders payment-stage-create');

        try {
            return PaymentStageResource::make(
                $this->orderService->addPaymentStage($order, $request->validated(), $request->user())
            );
        } catch (Exception $e) {
            Log::error($e->getTraceAsString());
            return $this->makeErrorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Delete payment stage
     *
     * @param Order $order
     * @param PaymentStage $paymentStage
     * @return JsonResponse
     * @throws AuthorizationException
     *
     * @OA\Delete(
     *     path="/api/orders/{orderId}/payment-stage",
     *     tags={"Orders"},
     *     summary="Delete payment stage",
     *     operationId="Delete payment stage",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Response(
     *         response=204,
     *         description="Successful operation",
     *     ),
     * )
     *
     */
    public function deletePaymentStage(Order $order, PaymentStage $paymentStage): JsonResponse
    {
        $this->authorize('orders payment-stage-delete');

        try {
            $this->orderService->deletePaymentStage($order, $paymentStage);
            return $this->makeSuccessResponse(null, Response::HTTP_NO_CONTENT);
        } catch (Exception $e) {
            Log::error($e->getTraceAsString());
            return $this->makeErrorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param SendSignatureLinkRequest $request
     * @param Order $order
     * @return JsonResponse
     * @OA\Post(
     *     path="/api/orders/{orderId}/send-signature-link",
     *     tags={"Orders"},
     *     summary="Send signature link by dispatcher",
     *     operationId="Send signature link by dispatcher",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="inspection_location", in="query", description="Inspection location", required=true,
     *          @OA\Schema(type="string", enum={"delivery", "pickup"})
     *     ),
     *     @OA\Parameter(name="email", in="query", description="Recipient email", required=true,
     *          @OA\Schema(type="string", format="email")
     *     ),
     *     @OA\Response(response=204, description="Successful operation"),
     * )
     */
    public function sendSignatureLink(SendSignatureLinkRequest $request, Order $order): JsonResponse
    {
        try {
            $this->orderService->sendSignatureLink($order, $request->validated());

            return $this->makeSuccessResponse(null, Response::HTTP_NO_CONTENT);
        } catch (OrderAlreadySigned|OrderHasNotHadInspectionYet $e) {
            return $this->makeErrorResponse($e->getMessage(), Response::HTTP_FAILED_DEPENDENCY);
        } catch (Exception $e) {
            return $this->makeErrorResponse(null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param Order $order
     * @param GeneratePdfService $service
     *
     * @OA\Get(
     *     path="/api/orders/{orderId}/available-invoices",
     *     tags={"Orders"},
     *     summary="Get available invoices",
     *     operationId="Get available invoices",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(
     *          name="orderId",
     *          in="path",
     *          description="Order id",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *     ),
     *     @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema (
     *                  type="object",
     *                  required={"data"},
     *                  @OA\Property (
     *                      property="data",
     *                      description="Response data",
     *                      type="array",
     *                      nullable=true,
     *                      @OA\Items (ref="#/components/schemas/OrderAvailableInvoceResource"),
     *                  )
     *              )
     *          )
     *    ),
     * )
     * @return JsonResponse|AnonymousResourceCollection
     */
    public function availableInvoices(Order $order, GeneratePdfService $service)
    {
        try {
            $invoices = $service->getAvailableInvoices($order);

            if ($invoices === null) {
                return response()->json(['data' => null]);
            }

            return OrderAvailableInvoicesResource::collection($invoices);
        } catch (Exception $e) {
            Log::error($e);

            return $this->makeErrorResponse(null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param OrdersExportRequest $request
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     *
     * @OA\Get(path="/api/orders/export",
     *     tags={"Orders export"},
     *     summary="Returns export file",
     *     operationId="Orders export",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="date_from", in="query", description="Export date from", required=true,
     *          @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(name="date_to", in="query", description="Export date to", required=true,
     *          @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response=200, description="Successful operation",),
     * )
     */
    public function export(OrdersExportRequest $request)
    {
        try {
            $dateFrom = Carbon::createFromTimestamp(strtotime($request->date_from))->startOfDay();
            $dateTo = Carbon::createFromTimestamp(strtotime($request->date_to))->endOfDay();

            return $this->orderService->export($dateFrom, $dateTo);
        } catch (Exception $e) {
            Log::error($e);

            return $this->makeErrorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
