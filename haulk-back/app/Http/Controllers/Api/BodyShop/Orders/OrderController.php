<?php

namespace App\Http\Controllers\Api\BodyShop\Orders;

use App\Exceptions\Contact\SenderDoesNotHaveEmail;
use App\Http\Controllers\ApiController;
use App\Http\Requests\BodyShop\Orders\GenerateInvoiceRequest;
use App\Http\Requests\BodyShop\Orders\OrderChangeStatusRequest;
use App\Http\Requests\BodyShop\Orders\OrderHistoryRequest;
use App\Http\Requests\BodyShop\Orders\OrderIndexRequest;
use App\Http\Requests\BodyShop\Orders\OrderReassignMechanicRequest;
use App\Http\Requests\BodyShop\Orders\OrderReportRequest;
use App\Http\Requests\BodyShop\Orders\OrderRequest;
use App\Http\Requests\BodyShop\Orders\OrderRestoreRequest;
use App\Http\Requests\BodyShop\Orders\PaymentRequest;
use App\Http\Requests\BodyShop\Orders\SendDocsRequest;
use App\Http\Requests\Users\SingleAttachmentRequest;
use App\Http\Resources\BodyShop\Orders\OrderPaginateResource;
use App\Http\Resources\BodyShop\Orders\OrderReportPaginateResource;
use App\Http\Resources\BodyShop\Orders\OrderReportTotalResource;
use App\Http\Resources\BodyShop\Orders\OrderResource;
use App\Http\Resources\BodyShop\Orders\PaymentResource;
use App\Http\Resources\BodyShop\History\HistoryListResource;
use App\Http\Resources\BodyShop\History\HistoryPaginatedResource;
use App\Models\BodyShop\Orders\Order;
use App\Models\BodyShop\Orders\Payment;
use App\Models\History\History;
use App\Services\BodyShop\Orders\InvoiceService;
use App\Services\BodyShop\Orders\OrderService;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class OrderController extends ApiController
{
    protected OrderService $service;

    public function __construct(OrderService $service)
    {
        parent::__construct();

        $this->service = $service->setUser(authUser());
    }

    /**
     * @param OrderIndexRequest $request
     * @return AnonymousResourceCollection
     *
     * @OA\Get(
     *     path="/api/body-shop/orders",
     *     tags={"Orders Body Shop"},
     *     summary="Get orders paginated list",
     *     operationId="Get orders data",
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
     *     @OA\Parameter(  name="q", in="query", description="Scope for search by vin, uinit_munber, order number", required=false,
     *          @OA\Schema( type="string", default="name",)
     *     ),
     *     @OA\Parameter(name="mechanic_id", in="query", description="Order mechanic id", required=false,
     *          @OA\Schema( type="integer", default="1", )
     *     ),
     *     @OA\Parameter(name="vehicle_make", in="query", description="Vehicle make", required=false,
     *          @OA\Schema( type="string", default="name",)
     *     ),
     *     @OA\Parameter(name="vehicle_model", in="query", description="Vehicle model", required=false,
     *          @OA\Schema( type="string", default="name",)
     *     ),
     *     @OA\Parameter(name="vehicle_year", in="query", description="Vehicle year", required=false,
     *          @OA\Schema( type="string", default="name",)
     *     ),
     *     @OA\Parameter(name="status", in="query", description="Order status", required=false,
     *          @OA\Schema( type="string", enum={"new","in_process","finished","deleted"})
     *     ),
     *     @OA\Parameter(name="payment_status", in="query", description="Order payment status", required=false,
     *          @OA\Schema( type="string", enum={"paid","not_paid","billed","not_billed","overdue","not_overdue"})
     *     ),
     *     @OA\Parameter(name="date_from", in="query", description="Order implementation date from", required=false,
     *          @OA\Schema( type="string", default="2023-02-13 10:00",)
     *     ),
     *     @OA\Parameter(name="date_to", in="query", description="Order implementation date to", required=false,
     *          @OA\Schema( type="string", default="2023-02-13 10:00",)
     *     ),
     *     @OA\Parameter(name="inventory_id", in="query", description="Order inventory id", required=false,
     *          @OA\Schema( type="integer", default="1", )
     *     ),
     *     @OA\Parameter(name="truck_id", in="query", description="Order truck id", required=false,
     *          @OA\Schema( type="integer", default="1", )
     *     ),
     *     @OA\Parameter(name="trailer_id", in="query", description="Order trailer id", required=false,
     *          @OA\Schema( type="integer", default="1", )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/OrderPaginateBS")
     *     ),
     * )
     * @todo moved
     */
    public function index(OrderIndexRequest $request)
    {
        $orders = Order::query()
            ->select('*')
            ->filter($request->validated())
            ->orderByDefault()
            ->paginate($request->per_page);

        return OrderPaginateResource::collection($orders);
    }

    /**
     * @param OrderRequest $request
     * @return OrderResource|JsonResponse
     * @throws AuthorizationException
     * @throws Throwable
     * @OA\Post(path="/api/body-shop/orders", tags={"Orders Body Shop"}, summary="Create Order", operationId="Create Order", deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/ValidateOnly"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="truck_id", in="query", description="Truck id", required=false,
     *          @OA\Schema(type="integer", default="1",)
     *     ),
     *     @OA\Parameter(name="trailer_id", in="query", description="Trailer id", required=false,
     *          @OA\Schema(type="integer", default="1",)
     *     ),
     *     @OA\Parameter(name="discount", in="query", description="Order discount, %", required=false,
     *          @OA\Schema(type="number", default="10",)
     *     ),
     *     @OA\Parameter(name="tax_labor", in="query", description="Orer tax labor, %", required=false,
     *          @OA\Schema(type="number", default="10",)
     *     ),
     *     @OA\Parameter(name="tax_inventory", in="query", description="Order tax inventory, %", required=false,
     *          @OA\Schema(type="number", default="10",)
     *     ),
     *     @OA\Parameter(name="implementation_date", in="query", description="Order implementation date", required=true,
     *          @OA\Schema(type="string", default="2023-02-13 10:00",)
     *     ),
     *     @OA\Parameter(name="mechanic_id", in="query", description="Order mechanic id", required=true,
     *          @OA\Schema(type="integer", default="1",)
     *     ),
     *     @OA\Parameter(name="notes", in="query", description="Order notes", required=false,
     *          @OA\Schema(type="string", default="test",)
     *     ),
     *     @OA\Parameter(name="due_date", in="query", description="Order due date", required=true,
     *          @OA\Schema(type="string", default="2023-02-13",)
     *     ),
     *     @OA\Parameter(name="types_of_work", in="query", description="Order Types Of Work", required=true,
     *          @OA\Schema(type="array",
     *              @OA\Items(
     *                  allOf={
     *                      @OA\Schema(
     *                          @OA\Property(property="name", type="string", description="Type Of Work name"),
     *                          @OA\Property(property="duration", type="string", description="Type Of Work duration"),
     *                          @OA\Property(property="hourly_rate", type="number", description="Type Of Work rate"),
     *                          @OA\Property(property="inventories", type="array", description="Type Of Work inventories",
     *                                  @OA\Items(
     *                                      allOf={
     *                                          @OA\Schema(
     *                                              @OA\Property(property="id", type="integer", description="Type Of Work inventory id"),
     *                                              @OA\Property(property="quantity", type="number", description="Type Of Work inventory quantity"),
     *                                         )
     *                                     }
     *                              )
     *                         ),
     *                     )
     *                 }
     *              )
     *          )
     *     ),
     *     @OA\Response(response=201, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/OrderBS")
     *     ),
     * )
     * @todo moved
     */
    public function store(OrderRequest $request)
    {
        $this->authorize('orders-bs create');

        try {
            $order = $this->service->create($request->dto());

            return OrderResource::make($order);
        } catch (Exception $exception) {
            Log::error($exception);

            return $this->makeErrorResponse($exception->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     *
     * @OA\Get(
     *     path="/api/body-shop/orders/{orderId}",
     *     tags={"Orders Body Shop"},
     *     summary="Get order record",
     *     operationId="Get order record",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="id", in="path", description="Order id", required=true,
     *          @OA\Schema( type="integer",)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/OrderBS")
     *     ),
     * )
     * @param Order $order
     * @return OrderResource
     * @throws AuthorizationException
     *
     * @todo moved
     */
    public function show(Order $order): OrderResource
    {
        $this->authorize('orders-bs read');

        return OrderResource::make($order);
    }

    /**
     * @param OrderRequest $request
     * @return OrderResource|JsonResponse
     * @throws AuthorizationException
     * @throws Throwable
     * @OA\Post(path="/api/body-shop/orders/orderId", tags={"Orders Body Shop"}, summary="Update Order", operationId="Update Order", deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/ValidateOnly"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="truck_id", in="query", description="Truck id", required=false,
     *          @OA\Schema(type="integer", default="1",)
     *     ),
     *     @OA\Parameter(name="trailer_id", in="query", description="Trailer id", required=false,
     *          @OA\Schema(type="integer", default="1",)
     *     ),
     *     @OA\Parameter(name="discount", in="query", description="Order discount, %", required=false,
     *          @OA\Schema(type="number", default="10",)
     *     ),
     *     @OA\Parameter(name="tax_labor", in="query", description="Orer tax labor, %", required=false,
     *          @OA\Schema(type="number", default="10",)
     *     ),
     *     @OA\Parameter(name="tax_inventory", in="query", description="Order tax inventory, %", required=false,
     *          @OA\Schema(type="number", default="10",)
     *     ),
     *     @OA\Parameter(name="implementation_date", in="query", description="Order implementation date", required=true,
     *          @OA\Schema(type="string", default="2023-02-13 10:00",)
     *     ),
     *     @OA\Parameter(name="mechanic_id", in="query", description="Order mechanic id", required=true,
     *          @OA\Schema(type="integer", default="1",)
     *     ),
     *     @OA\Parameter(name="notes", in="query", description="Order notes", required=false,
     *          @OA\Schema(type="string", default="test",)
     *     ),
     *     @OA\Parameter(name="due_date", in="query", description="Order due date", required=true,
     *          @OA\Schema(type="string", default="2023-02-13",)
     *     ),
     *     @OA\Parameter(name="need_to_update_prices", in="query", description="Is need to update inventory prices", required=false,
     *          @OA\Schema(type="boolean")
     *     ),
     *     @OA\Parameter(name="types_of_work", in="query", description="Order Types Of Work", required=true,
     *          @OA\Schema(type="array",
     *              @OA\Items(
     *                  allOf={
     *                      @OA\Schema(
     *                          @OA\Property(property="id", type="integer", description="Type Of Work id"),
     *                          @OA\Property(property="name", type="string", description="Type Of Work name"),
     *                          @OA\Property(property="duration", type="string", description="Type Of Work duration"),
     *                          @OA\Property(property="hourly_rate", type="number", description="Type Of Work rate"),
     *                          @OA\Property(property="inventories", type="array", description="Type Of Work inventories",
     *                                  @OA\Items(
     *                                      allOf={
     *                                          @OA\Schema(
     *                                              @OA\Property(property="id", type="integer", description="Type Of Work inventory id"),
     *                                              @OA\Property(property="quantity", type="number", description="Type Of Work inventory quantity"),
     *                                         )
     *                                     }
     *                              )
     *                         ),
     *                     )
     *                 }
     *              )
     *          )
     *     ),
     *     @OA\Response(response=201, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/OrderBS")
     *     ),
     * )
     * @todo moved
     */
    public function update(OrderRequest $request, Order $order)
    {
        $this->authorize('orders-bs update');

        if ($order->isFinished()) {
            return $this->makeErrorResponse(trans('Finished order can\'t be edited'), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($order->isPaid()) {
            return $this->makeErrorResponse(trans('Order is paid. Please delete the payment first to edit the order. '), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $order = $this->service->update($order, $request->dto());

            return OrderResource::make($order);
        } catch (Exception $exception) {
            Log::error($exception);

            return $this->makeErrorResponse($exception->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/body-shop/orders/{orderId}/attachments",
     *     tags={"Orders Body Shop"},
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
     *         @OA\JsonContent(ref="#/components/schemas/OrderBS")
     *     ),
     * )
     *
     * @param SingleAttachmentRequest $request
     * @param Order $order
     * @return JsonResponse|OrderResource
     * @throws AuthorizationException
     * @todo moved
     */
    public function addAttachment(SingleAttachmentRequest $request, Order $order)
    {
        $this->authorize('orders-bs update');

        if ($order->isFinished()) {
            return $this->makeErrorResponse(trans('Finished order can\'t be edited'), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            return new OrderResource(
                $this->service->addAttachment(
                    $order,
                    $request->attachment,
                    true
                )
            );
        } catch (Exception $e) {
            Log::error($e->getTraceAsString());

            return $this->makeErrorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     *
     * @OA\Delete(
     *     path="/api/body-shop/orders/{orderId}/attachments/{attachmentId}",
     *     tags={"Orders Body Shop"},
     *     summary="Delete attachment from order",
     *     operationId="Delete attachment",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Response(response=204, description="Successful operation"),
     * )
     * @todo moved
     */
    public function deleteAttachment(Order $order, int $id)
    {
        $this->authorize('orders-bs update');

        if ($order->isFinished()) {
            return $this->makeErrorResponse(trans('Finished order can\'t be edited'), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $this->service->deleteAttachment($order, $id);

            return $this->makeSuccessResponse(null, Response::HTTP_NO_CONTENT);
        } catch (Exception $e) {
            Log::error($e->getTraceAsString());
            return $this->makeErrorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
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
     *     path="/api/body-shop/orders/{orderId}/history",
     *     tags={"Orders Body Shop"},
     *     summary="Get order history",
     *     operationId="Get order history",
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
    public function history(int $orderId)
    {
        $this->authorize('orders-bs read');

        try {
            return HistoryListResource::collection(
                $this->service->history($orderId)
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
     *     path="/api/body-shop.orders/{orderId}/history-detailed",
     *     tags={"Orders Body Shop"},
     *     summary="Get order history detailed",
     *     operationId="Get order history detailed",
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
     *     @OA\Parameter( name="per_page", in="query", description="per page", required=false,
     *          @OA\Schema(type="integer", default="10")
     *     ),
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/HistoryPaginatedResourceBS")
     *     ),
     * )
     */
    public function historyDetailed(int $orderId, OrderHistoryRequest $request)
    {
        $this->authorize('orders-bs read');

        try {
            $history = History::query()
                ->where(
                    [
                        ['model_id', $orderId],
                        ['model_type', Order::class],
                    ]
                )
                ->whereType(History::TYPE_CHANGES)
                ->filter($request->validated())
                ->latest('id')
                ->paginate($request->per_page);

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
     * Manually change order status
     *
     * @param Order $order
     * @param OrderChangeStatusRequest $request
     * @return OrderResource|JsonResponse
     * @throws AuthorizationException|Throwable
     *
     * @OA\Put(
     *     path="/api/body-shop/orders/{orderId}/change-status",
     *     tags={"Orders Body Shop"},
     *     summary="Manually change order status",
     *     operationId="Manually change order status",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="status", in="query", description="Order status", required=true,
     *          @OA\Schema(type="string", enum={"new", "in_process", "finished"})
     *     ),
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/OrderBS")
     *     ),
     * )
     *
     * @todo moved
     */
    public function changeStatus(Order $order, OrderChangeStatusRequest $request)
    {
        $this->authorize('orders-bs change-status');

        try {
            return new OrderResource(
                $this->service->changeStatus($order, $request->input('status'))
            );
        } catch (Exception $e) {
            Log::error($e->getTraceAsString());
            return $this->makeErrorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Reassign mechanic
     *
     * @param Order $order
     * @param OrderReassignMechanicRequest $request
     * @return OrderResource|JsonResponse
     * @throws AuthorizationException|Throwable
     *
     * @OA\Put(
     *     path="/api/body-shop/orders/{orderId}/reassign-mechanic",
     *     tags={"Orders Body Shop"},
     *     summary="Reassign mechanic",
     *     operationId="Reassign mechanic",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="mechanic_id", in="query", description="Mechanic id", required=true,
     *          @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/OrderBS")
     *     ),
     * )
     *
     * @todo moved
     */
    public function reassignMechanic(Order $order, OrderReassignMechanicRequest $request)
    {
        $this->authorize('orders-bs reassign-mechanic');

        try {
            return new OrderResource(
                $this->service->reassignMechanic($order, $request->input('mechanic_id'))
            );
        } catch (Exception $e) {
            Log::error($e->getTraceAsString());
            return $this->makeErrorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get Invoice pdf file
     *
     * @param Order $order
     * @param InvoiceService $invoiceService
     * @return JsonResponse|StreamedResponse
     *
     * @OA\Get(
     *     path="/api/body-shop/orders/{orderId}/generate-invoice",
     *     tags={"Orders Body Shop"},
     *     summary="Get Invoice pdf file",
     *     operationId="Get Invoice pdf file",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="invoice_date", in="query", description="Invoice date. Format m/d/Y", required=false,
     *          @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *     ),
     * )
     */
    public function generateInvoice(Order $order, GenerateInvoiceRequest $request, InvoiceService $invoiceService)
    {
        $this->authorize('orders-bs generate-invoice');

        try {
           return response()->streamDownload(function () use ($order, $invoiceService, $request) {
                $invoiceService->generateInvoicePdf(
                    $order,
                    $request->invoice_date ? fromBSTimezone('m/d/Y', $request->invoice_date) : null ,
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
     * @param Order $order
     * @return JsonResponse
     * @throws AuthorizationException
     * @OA\Delete(
     *     path="/api/body-shop/orders/{orderId}",
     *     tags={"Orders Body Shop"},
     *     summary="Delete order",
     *     operationId="Delete order",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Response(response=204, description="Successful operation"),
     * )
     *
     * @todo moved
     */
    public function destroy(Order $order): JsonResponse
    {
        $this->authorize('orders-bs delete');

        if ($order->isFinished()) {
            return $this->makeErrorResponse(trans('Finished order can\'t be deleted'), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $this->service->deleteOrder($order);
            return $this->makeSuccessResponse(null, Response::HTTP_NO_CONTENT);
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
     *     path="/api/body-shop/orders/{orderId}/restore",
     *     tags={"Orders Body Shop"},
     *     summary="Restore deleted order",
     *     operationId="Restore deleted order",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/OrderBS")
     *     ),
     * )
     * @todo moved
     */
    public function restoreOrder(int $orderId, OrderRestoreRequest $request)
    {
        if (!($order = Order::onlyTrashed()->find($request->order))) {
            return $this->makeErrorResponse(null, Response::HTTP_NOT_FOUND);
        }

        $this->authorize('orders-bs restore');

        try {
            return OrderResource::make(
                $this->service->restoreOrder($order)
            );
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
     * @OA\GET(
     *     path="/api/body-shop/orders/{orderId}/view-deleted",
     *     tags={"Orders Body Shop"},
     *     summary="View deleted order",
     *     operationId="View deleted order",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/OrderBS")
     *     ),
     * )
     * @todo moved
     */
    public function viewDeletedOrder(int $orderId)
    {
        $this->authorize('orders-bs restore');

        if (!($order = Order::onlyTrashed()->find($orderId))) {
            return $this->makeErrorResponse(null, Response::HTTP_NOT_FOUND);
        }

        try {
            return OrderResource::make($order);
        } catch (Exception $e) {
            Log::error($e->getTraceAsString());
            return $this->makeErrorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param OrderRequest $request
     * @return OrderResource|JsonResponse
     * @throws AuthorizationException
     * @throws Throwable
     * @OA\Post(path="/api/body-shop/orders/orderId/restore-with-editing",
     *     tags={"Orders Body Shop"},
     *     summary="Restore with editing Order",
     *     operationId="Restore with editing Order",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/ValidateOnly"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="truck_id", in="query", description="Truck id", required=false,
     *          @OA\Schema(type="integer", default="1",)
     *     ),
     *     @OA\Parameter(name="trailer_id", in="query", description="Trailer id", required=false,
     *          @OA\Schema(type="integer", default="1",)
     *     ),
     *     @OA\Parameter(name="discount", in="query", description="Order discount, %", required=false,
     *          @OA\Schema(type="number", default="10",)
     *     ),
     *     @OA\Parameter(name="tax_labor", in="query", description="Orer tax labor, %", required=false,
     *          @OA\Schema(type="number", default="10",)
     *     ),
     *     @OA\Parameter(name="tax_inventory", in="query", description="Order tax inventory, %", required=false,
     *          @OA\Schema(type="number", default="10",)
     *     ),
     *     @OA\Parameter(name="implementation_date", in="query", description="Order implementation date", required=true,
     *          @OA\Schema(type="string", default="2023-02-13 10:00",)
     *     ),
     *     @OA\Parameter(name="mechanic_id", in="query", description="Order mechanic id", required=true,
     *          @OA\Schema(type="integer", default="1",)
     *     ),
     *     @OA\Parameter(name="notes", in="query", description="Order notes", required=false,
     *          @OA\Schema(type="string", default="test",)
     *     ),
     *     @OA\Parameter(name="due_date", in="query", description="Order due date", required=true,
     *          @OA\Schema(type="string", default="2023-02-13",)
     *     ),
     *     @OA\Parameter(name="types_of_work", in="query", description="Order Types Of Work", required=false,
     *          @OA\Schema(type="array",
     *              @OA\Items(
     *                  allOf={
     *                      @OA\Schema(
     *                          @OA\Property(property="id", type="integer", description="Type Of Work id"),
     *                          @OA\Property(property="name", type="string", description="Type Of Work name"),
     *                          @OA\Property(property="duration", type="string", description="Type Of Work duration"),
     *                          @OA\Property(property="hourly_rate", type="number", description="Type Of Work rate"),
     *                          @OA\Property(property="inventories", type="array", description="Type Of Work inventories",
     *                                  @OA\Items(
     *                                      allOf={
     *                                          @OA\Schema(
     *                                              @OA\Property(property="id", type="integer", description="Type Of Work inventory id"),
     *                                              @OA\Property(property="quantity", type="number", description="Type Of Work inventory quantity"),
     *                                         )
     *                                     }
     *                              )
     *                         ),
     *                     )
     *                 }
     *              )
     *          )
     *     ),
     *     @OA\Response(response=201, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/OrderBS")
     *     ),
     * )
     */
    public function restoreOrderWithEditing(int $orderId, OrderRequest $request)
    {
        if (!($order = Order::onlyTrashed()->find($request->order))) {
            return $this->makeErrorResponse(null, Response::HTTP_NOT_FOUND);
        }

        $this->authorize('orders-bs restore');

        try {
            $order = $this->service->restoreOrderWithEditing($order, $request->dto());

            return OrderResource::make($order);
        } catch (Exception $exception) {
            Log::error($exception);

            return $this->makeErrorResponse($exception->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
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
     *     path="/api/body-shop/orders/{orderId}/permanently",
     *     tags={"Orders Body Shop"},
     *     summary="Delete order permanently",
     *     operationId="Delete order permanently",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Response(response=204, description="Successful operation"),
     * )
     * @todo moved
     */
    public function deletePermanently(int $orderId): JsonResponse
    {
        $this->authorize('orders-bs delete-permanently');

        if (!($order = Order::onlyTrashed()->find($orderId))) {
            return $this->makeErrorResponse(null, Response::HTTP_NOT_FOUND);
        }

        try {
            $this->service->deleteOrderPermanently($order);
            return $this->makeSuccessResponse(null, Response::HTTP_NO_CONTENT);
        } catch (Exception $e) {
            Log::error($e->getTraceAsString());

            return $this->makeErrorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Send Invoice pdf file
     *
     * @param SendDocsRequest $request
     * @param InvoiceService $invoiceService
     * @return JsonResponse
     *
     * @OA\Post(
     *     path="/api/body-shop/orders/{orderId}/send-docs",
     *     tags={"Orders Body Shop"},
     *     summary="Send Invoice pdf files",
     *     operationId="Send Invoice files",
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
     *                  required={"content", "invoice_date"},
     *                  @OA\Property (
     *                      property="recipient_email",
     *                      description="Array of recipient emails",
     *                      type="array",
     *                      nullable=false,
     *                      @OA\Items(
     *                          type="string",
     *                          example="my.email@gmail.com"
     *                      )
     *                  ),
     *                  @OA\Property (
     *                      property="content",
     *                      description="Selected sending docs",
     *                      type="array",
     *                      nullable=false,
     *                      @OA\Items (type="string", enum={"invoice"})
     *                  ),
     *                  @OA\Property (
     *                      property="invoice_date",
     *                      type="string",
     *                      nullable=true,
     *                      description="Invoice date (required if 'content' contains 'invoice'). Format m/d/Y",
     *                      example="12/21/2021"
     *                  ),
     *             )
     *          )
     *     ),
     *     @OA\Response(response=200, description="Successful operation"),
     * )
     * @throws Throwable
     */
    public function sendDocs(Order $order, SendDocsRequest $request, InvoiceService $invoiceService): JsonResponse
    {
        $this->authorize('orders-bs send-documents');
        try {
            $invoiceService->sendDocs($request->user(), $order, $request->dto());

            return $this->makeSuccessResponse(null, Response::HTTP_OK);
        } catch (SenderDoesNotHaveEmail $e) {
            return $this->makeErrorResponse($e->getMessage(), Response::HTTP_FAILED_DEPENDENCY);
        } catch (Exception $e) {
            Log::error($e->getTraceAsString());
            return $this->makeErrorResponse(null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Add payment
     *
     * @param Order $order
     * @param PaymentRequest $request
     * @return JsonResponse|PaymentResource
     * @throws AuthorizationException
     *
     * @OA\Post(
     *     path="/api/body-shop/orders/{orderId}/payment",
     *     tags={"Orders Body Shop"},
     *     summary="Add payment",
     *     operationId="Add payment",
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
     *     @OA\Parameter(name="payment_method", in="query", description="", required=true,
     *          @OA\Schema(type="string", enum={"cash", "credit_card", "miney_order", "quick_pay", "cashapp", "paypal", "venmo", "zelle"})
     *     ),
     *     @OA\Parameter(name="notes", in="query", description="", required=false,
     *          @OA\Schema(type="string",)
     *     ),
     *     @OA\Parameter(name="reference_number", in="query", description="", required=false,
     *          @OA\Schema(type="string",)
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/PaymentResourceRawBS")
     *     ),
     * )
     *@todo moved
     */
    public function addPayment(Order $order, PaymentRequest $request)
    {
        $this->authorize('orders-bs create-payment');

        try {
            return PaymentResource::make(
                $this->service->createPayment($order, $request->validated())
            );
        } catch (Exception $e) {
            Log::error($e->getTraceAsString());
            return $this->makeErrorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Delete payment
     *
     * @param Order $order
     * @param Payment $payment
     * @return JsonResponse
     * @throws AuthorizationException
     *
     * @OA\Delete(
     *     path="/api/body-shop/orders/{orderId}/payment{paymentId}",
     *     tags={"Orders Body Shop"},
     *     summary="Delete payment",
     *     operationId="Delete payment",
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
     * @todo moved
     */
    public function deletePayment(Order $order, Payment $payment): JsonResponse
    {
        $this->authorize('orders-bs delete-payment');

        try {
            $this->service->deletePayment($order, $payment);
            return $this->makeSuccessResponse(null, Response::HTTP_NO_CONTENT);
        } catch (Exception $e) {
            Log::error($e->getTraceAsString());
            return $this->makeErrorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param OrderReportRequest $request
     * @return AnonymousResourceCollection
     *
     * @OA\Get(
     *     path="/api/body-shop/orders/report",
     *     tags={"Orders Body Shop"},
     *     summary="Get orders report",
     *     operationId="Get orders report data",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="page", in="query", description="Page number", required=false,
     *          @OA\Schema( type="integer", default="5")
     *     ),
     *     @OA\Parameter(name="per_page", in="query", description="States per page", required=false,
     *          @OA\Schema( type="integer", default="12")
     *     ),
     *     @OA\Parameter(name="order_by", in="query", description="Order by", required=false,
     *          @OA\Schema(type="string", enum={"current_due","past_due","total_due"})
     *     ),
     *     @OA\Parameter(name="order_type", in="query", description="Order by type", required=false,
     *          @OA\Schema(type="string", enum={"asc","desc"})
     *     ),
     *     @OA\Parameter(name="q", in="query", description="Scope for search by vin, order number", required=false,
     *          @OA\Schema(type="string", default="name",)
     *     ),
     *     @OA\Parameter(name="statuses", in="query", description="Order status", required=false,
     *          @OA\Schema(type="array",
     *              @OA\Items(allOf={@OA\Schema(type="string", enum={"new","in_process","finished"})})
     *          )
     *     ),
     *     @OA\Parameter(name="payment_statuses", in="query", description="Order payment status", required=false,
     *          @OA\Schema(type="array",
     *              @OA\Items(allOf={@OA\Schema(type="string", enum={"paid","not_paid","billed","not_billed","overdue","not_overdue"})})
     *          )
     *     ),
     *     @OA\Parameter(name="implementation_date_from", in="query", description="Order implementation date from", required=false,
     *          @OA\Schema(type="string", default="2023-02-13 10:00",)
     *     ),
     *     @OA\Parameter(name="implementation_date_to", in="query", description="Order implementation date to", required=false,
     *          @OA\Schema(type="string", default="2023-02-13 10:00",)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/OrderReportPaginateBS")
     *     ),
     * )
     *
     * @todo moved
     */
    public function report(OrderReportRequest $request)
    {
        $this->authorize('reports-bs orders');

        $orders = Order::query()
            ->select('*')
            ->filter($request->validated())
            ->orderForReport($request->order_by ?? 'implementation_date', $request->order_type ?? 'desc')
            ->paginate($request->per_page);

        return OrderReportPaginateResource::collection($orders);
    }

    /**
     * @param OrderReportRequest $request
     * @return OrderReportTotalResource
     *
     * @OA\Get(
     *     path="/api/body-shop/orders/report-total",
     *     tags={"Orders Body Shop"},
     *     summary="Get orders report total amounts",
     *     operationId="Get orders report data",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="q", in="query", description="Scope for search by vin, order number", required=false,
     *          @OA\Schema(type="string", default="name",)
     *     ),
     *     @OA\Parameter(name="statuses", in="query", description="Order status", required=false,
     *          @OA\Schema(type="array",
     *              @OA\Items(allOf={@OA\Schema(type="string", enum={"new","in_process","finished"})})
     *          )
     *     ),
     *     @OA\Parameter(name="payment_statuses", in="query", description="Order payment status", required=false,
     *          @OA\Schema(type="array",
     *              @OA\Items(allOf={@OA\Schema(type="string", enum={"paid","not_paid","billed","not_billed","overdue","not_overdue"})})
     *          )
     *     ),
     *     @OA\Parameter(name="implementation_date_from", in="query", description="Order implementation date from", required=false,
     *          @OA\Schema(type="string", default="2023-02-13 10:00",)
     *     ),
     *     @OA\Parameter(name="implementation_date_to", in="query", description="Order implementation date to", required=false,
     *          @OA\Schema(type="string", default="2023-02-13 10:00",)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/OrderReportTotalBS")
     *     ),
     * )
     *
     * @todo moved
     */
    public function reportTotal(OrderReportRequest $request): OrderReportTotalResource
    {
        $this->authorize('reports-bs orders');

        $total = Order::query()
            ->selectRaw('
                SUM(profit) as total_profit,
                SUM(total_amount) as total_amount,
                SUM(debt_amount) as total_due,
                SUM(CASE WHEN due_date >= now() THEN debt_amount ELSE 0 END) as current_due,
                SUM(CASE WHEN due_date < now() THEN debt_amount ELSE 0 END) as past_due
            ')
            ->filter($request->validated())
            ->first();

        return OrderReportTotalResource::make($total);
    }
}
