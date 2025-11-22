<?php

namespace App\Http\Controllers\Api\Orders;

use App\Documents\Filters\Exceptions\DocumentFilterMethodNotFoundException;
use App\Exceptions\Contact\SenderDoesNotHaveEmail;
use App\Exceptions\Order\EmptyInvoiceTotalDue;
use App\Exceptions\Order\NotVinInspectionException;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Orders\DeliveryInspectExteriorRequest;
use App\Http\Requests\Orders\DriverDocumentRequest;
use App\Http\Requests\Orders\DriverPhotoRequest;
use App\Http\Requests\Orders\InspectDamageRequest;
use App\Http\Requests\Orders\InspectInteriorRequest;
use App\Http\Requests\Orders\InspectionSignatureRequest;
use App\Http\Requests\Orders\InspectVinRequest;
use App\Http\Requests\Orders\OrderCommentRequest;
use App\Http\Requests\Orders\PickupInspectExteriorRequest;
use App\Http\Requests\Orders\SendDocsMobileRequest;
use App\Http\Resources\Orders\OrderCommentResource;
use App\Http\Resources\Orders\OrderMobilePaginatedResource;
use App\Http\Resources\Orders\OrderResource;
use App\Http\Resources\Orders\VehicleResource;
use App\Models\Orders\Order;
use App\Models\Orders\Vehicle;
use App\Models\SendDocsDelay;
use App\Models\Users\User;
use App\Services\Orders\GeneratePdfService;
use App\Services\Orders\OrderService;
use App\Services\Orders\OrderStatusService;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Log;
use Throwable;

class OrderMobileController extends ApiController
{

    protected OrderService $orderService;

    protected ?OrderStatusService $orderStatusService = null;

    public function __construct(OrderService $orderService)
    {
        parent::__construct();

        $this->orderService = $orderService;

        $request = \request();

        if ($request instanceof Request) {
            $this->orderService->setUser($request->user(User::GUARD));
        }
    }

    protected function statusService(): OrderStatusService
    {
        if (!empty($this->orderStatusService)) {
            return $this->orderStatusService;
        }
        $this->orderStatusService = resolve(OrderStatusService::class);

        $this->orderStatusService->setUser(authUser());

        return $this->orderStatusService;
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return AnonymousResourceCollection
     *
     * @OA\Get(
     *     path="/api/mobile/orders",
     *     tags={"Mobile Orders"},
     *     summary="Get orders paginated list for driver",
     *     operationId="Get orders data",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="status", in="query", description="Order status", required=false,
     *          @OA\Schema(type="string", enum={"in_work","plan","history"})
     *     ),
     *     @OA\Parameter(name="date_from", in="query", description="Date from filtering (YYYY-MM-DD)", required=false,
     *          @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(name="date_to", in="query", description="Date to filtering (YYYY-MM-DD)", required=false,
     *          @OA\Schema(type="string", default="")
     *     ),
     *     @OA\Parameter(name="page", in="query", required=false, @OA\Schema(type="integer", default="5")),
     *     @OA\Parameter(name="per_page", in="query", required=false, @OA\Schema(type="integer", default="10")),
     *     @OA\Parameter(name="order_by", in="query", required=false, @OA\Schema(type="string", default="desc", enum ={"asc","desc"})),
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/OrderMobilePaginatedResource")
     *     ),
     * )
     *
     * @throws AuthorizationException|DocumentFilterMethodNotFoundException
     */
    public function index(Request $request, OrderService $service): AnonymousResourceCollection
    {
        $this->authorize('viewList', Order::class);
        return OrderMobilePaginatedResource::collection(
            $service->setUser($this->user())
                ->getMobileOrderList(
                    $request->get('status'),
                    (int)$request->get('per_page', 10),
                    (int)$request->get('page', 1)
                )
        );
    }

    /**
     * Display the specified resource.
     *
     * @param Order $order
     * @return JsonResponse|OrderResource
     *
     * @OA\Get(
     *     path="/api/mobile/orders/{orderId}",
     *     tags={"Mobile Orders"},
     *     summary="Get single order info",
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
        $this->authorize('viewAssignedToMe', $order);

        try {
            return OrderResource::make($order->loadMissingRelations());
        } catch (Exception $e) {
            Log::error($e->getTraceAsString());
            return $this->makeErrorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Get single order vehicle
     *
     * @param Order $order
     * @param Vehicle $vehicle
     * @return JsonResponse|VehicleResource
     *
     * @OA\Get(
     *     path="/api/mobile/orders/{orderId}/vehicles/{vehicleId}",
     *     tags={"Mobile Orders"},
     *     summary="Get single order vehicle",
     *     operationId="Get vehicle",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/VehicleResource")
     *     ),
     * )
     *
     * @throws AuthorizationException
     */
    public function getVehicle(Order $order, Vehicle $vehicle)
    {
        $this->authorize('orders inspection');
        $this->authorize('viewAssignedToMe', $order);

        if ($vehicle->order_id !== $order->id) {
            return $this->makeErrorResponse(trans('Vehicle not found.'), 422);
        }

        return new VehicleResource($vehicle);
    }

    /**
     * Mark seen by driver
     *
     * @param Order $order
     * @return JsonResponse
     *
     * @throws AuthorizationException
     * @throws Throwable
     * @OA\Put(
     *     path="/api/mobile/orders/{orderId}/seen-by-driver",
     *     tags={"Mobile Orders"},
     *     summary="Mark seen by driver",
     *     operationId="Mark seen by driver",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Response(response=200, description="Successful operation"),
     * )
     *
     */
    public function markSeenByDriver(Order $order): JsonResponse
    {
        $this->authorize('viewAssignedToMe', $order);

        try {
            $this->orderService->markSeenByDriver($order);

            return $this->makeSuccessResponse(null, Response::HTTP_OK);
        } catch (Exception $e) {
            Log::error($e->getTraceAsString());

            return $this->makeErrorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Send order in work
     *
     * @param Order $order
     * @return JsonResponse|OrderResource
     *
     * @OA\Put(
     *     path="/api/mobile/orders/{orderId}/send-in-work",
     *     tags={"Mobile Orders"},
     *     summary="Send order in work",
     *     operationId="Send order in work",
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
    public function sendInWork(Order $order)
    {
        $this->authorize('viewAssignedToMe', $order);

        if ($order->has_pickup_inspection || $order->has_pickup_signature) {
            return $this->makeErrorResponse(trans('This order can\'t be sent in work.'), 422);
        }

        try {
            return OrderResource::make(
                $this->orderService->sendInWork($order)->loadMissingRelations()
            );
        } catch (Exception $e) {
            Log::error($e->getTraceAsString());
            return $this->makeErrorResponse($e->getMessage(), 500);
        }
    }

    // attachments

    /**
     * Uploaded document
     *
     * @param Order $order
     * @param DriverDocumentRequest $request
     * @return JsonResponse|OrderResource
     *
     * @OA\Post(
     *     path="/api/mobile/orders/{orderId}/documents",
     *     tags={"Mobile Orders"},
     *     summary="Add document to order",
     *     operationId="Add document",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="document", in="query", required=false,
     *          @OA\Schema(type="file")
     *     ),
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/OrderResource")
     *     ),
     * )
     *
     * @throws AuthorizationException
     */
    public function addDocument(Order $order, DriverDocumentRequest $request)
    {
        $this->authorize('orders add-attachment');
        $this->authorize('viewAssignedToMe', $order);

        try {
            return OrderResource::make(
                $this->orderService->addDocument(
                    $order,
                    $request->validated()
                )->loadMissingRelations()
            );
        } catch (Exception $e) {
            return $this->makeErrorResponse(null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Delete driver uploaded document
     *
     * @param Order $order
     * @param int $id
     * @return JsonResponse
     *
     * @OA\Delete(
     *     path="/api/mobile/orders/{orderId}/documents/{documentId}",
     *     tags={"Mobile Orders"},
     *     summary="Delete document from order",
     *     operationId="Delete document",
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
    public function deleteDocument(Order $order, int $id): JsonResponse
    {
        $this->authorize('orders add-attachment');
        $this->authorize('viewAssignedToMe', $order);

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
     * Uploaded photo
     *
     * @param Order $order
     * @param DriverPhotoRequest $request
     * @return JsonResponse|OrderResource
     *
     * @OA\Post(
     *     path="/api/mobile/orders/{orderId}/photos",
     *     tags={"Mobile Orders"},
     *     summary="Add photo to order",
     *     operationId="Add photo",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="photo", in="query", required=false,
     *          @OA\Schema(type="file")
     *     ),
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/OrderResource")
     *     ),
     * )
     *
     * @throws AuthorizationException
     */
    public function addPhoto(Order $order, DriverPhotoRequest $request)
    {
        $this->authorize('orders add-attachment');
        $this->authorize('viewAssignedToMe', $order);

        try {
            return OrderResource::make(
                $this->orderService->addPhoto(
                    $order,
                    $request->validated()
                )->loadMissingRelations()
            );
        } catch (Exception $e) {
            Log::error($e->getTraceAsString());
            return $this->makeErrorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Delete driver uploaded photo
     *
     * @param Order $order
     * @param int $id
     * @return JsonResponse
     *
     * @OA\Delete(
     *     path="/api/mobile/orders/{orderId}/photos/{photoId}",
     *     tags={"Mobile Orders"},
     *     summary="Delete photo from order",
     *     operationId="Delete photo",
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
    public function deletePhoto(Order $order, int $id): JsonResponse
    {
        $this->authorize('orders add-attachment');
        $this->authorize('viewAssignedToMe', $order);

        try {
            $this->orderService->deletePhoto($order, $id);

            return $this->makeSuccessResponse(null, 204);
        } catch (Exception $e) {
            Log::error($e->getTraceAsString());
            return $this->makeErrorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Adds driver comment to the order
     *
     * @param Order $order
     * @param OrderCommentRequest $request
     * @param OrderCommentController $commentController
     * @return OrderCommentResource|JsonResponse
     *
     * @OA\Post(
     *     path="/api/mobile/orders/{orderId}/comments",
     *     tags={"Mobile Orders"},
     *     summary="Create comment",
     *     operationId="Create comment",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="comment", in="query", required=true,
     *          @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response=201, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/OrderCommentResource")
     *     ),
     * )
     *
     * @throws AuthorizationException
     * @throws Exception|Throwable
     */
    public function addComment(Order $order, OrderCommentRequest $request, OrderCommentController $commentController)
    {
        $this->authorize('viewAssignedToMe', $order);
        return $commentController->store($order, $request);
    }

    /**
     * Send vin scan during inspection
     *
     * @param Order $order
     * @param Vehicle $vehicle
     * @param InspectVinRequest $request
     * @return VehicleResource|JsonResponse
     *
     * @OA\Post(
     *     path="/api/mobile/orders/{orderId}/vehicles/{vehicleId}/inspect-vin",
     *     tags={"Mobile Orders"},
     *     summary="Send vin scan during inspection",
     *     operationId="Send vin scan",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="vin", in="query", description="scanned vin code", required=false,
     *          @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(name="vin_scan", in="query", description="vin photo", required=false,
     *          @OA\Schema(type="file",)
     *     ),
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/VehicleResource")
     *     ),
     * )
     *
     * @throws AuthorizationException
     * @throws Throwable
     */
    public function inspectVin(Order $order, Vehicle $vehicle, InspectVinRequest $request)
    {
        $this->authorize('orders inspection');
        $this->authorize('viewAssignedToMe', $order);

        if ($vehicle->order_id !== $order->id) {
            return $this->makeErrorResponse(
                trans('Vehicle not found.'),
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        try {
            return new VehicleResource(
                $this->orderService->inspectVin(
                    $order,
                    $vehicle,
                    $request->validated()
                )
            );
        } catch (Exception $e) {
            Log::error($e->getTraceAsString());
            return $this->makeErrorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Send damage photo during inspection
     *
     * @param Order $order
     * @param Vehicle $vehicle
     * @param InspectDamageRequest $request
     * @return VehicleResource|JsonResponse
     *
     * @OA\Post(
     *     path="/api/mobile/orders/{orderId}/vehicles/{vehicleId}/inspect-pickup-damage",
     *     tags={"Mobile Orders"},
     *     summary="Send damage photo during pickup inspection",
     *     operationId="Send damage photo",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="damage_labels", in="query", required=false,
     *          @OA\Schema(type="array", @OA\Items(type="string"))
     *     ),
     *     @OA\Parameter(name="damage_photo", in="query", required=true,
     *          @OA\Schema(type="file")
     *     ),
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/VehicleResource")
     *     ),
     * )
     */
    public function inspectPickupDamage(Order $order, Vehicle $vehicle, InspectDamageRequest $request)
    {
        try {
            return new VehicleResource($this->orderService->inspectPickupDamage($vehicle, $request->dto()));
        } catch (NotVinInspectionException $e) {
            return $this->makeErrorResponse($e->getMessage(), Response::HTTP_FAILED_DEPENDENCY);
        } catch (Exception $e) {
            Log::error($e->getTraceAsString());
            return $this->makeErrorResponse(null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Send vehicle photo to inspection
     *
     * @param Order $order
     * @param Vehicle $vehicle
     * @param PickupInspectExteriorRequest $request
     * @return VehicleResource|JsonResponse
     *
     * @OA\Post(
     *     path="/api/mobile/orders/{orderId}/vehicles/{vehicleId}/inspect-pickup-exterior",
     *     tags={"Mobile Orders"},
     *     summary="Send vehicle photo to pickup inspection",
     *     operationId="Upload vehicle pickup photo",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="photo_id", in="query", description="photo id (from 1 to max photos)", required=true,
     *          @OA\Schema(type="number",)
     *     ),
     *     @OA\Parameter(name="inspection_photo", in="query", description="vehicle photo", required=true,
     *          @OA\Schema(type="file",)
     *     ),
     *     @OA\Parameter(name="photo_lat", in="query", description="vehicle photo latitude", required=true,
     *          @OA\Schema(type="number",)
     *     ),
     *     @OA\Parameter(name="photo_lng", in="query", description="vehicle photo longitude", required=true,
     *          @OA\Schema(type="number",)
     *     ),
     *     @OA\Parameter(name="photo_timestamp", in="query", description="vehicle photo timestamp", required=false,
     *          @OA\Schema(type="integer",)
     *     ),
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/VehicleResource")
     *     ),
     * )
     *
     * @throws AuthorizationException
     * @throws Throwable
     */
    public function inspectPickupExterior(Order $order, Vehicle $vehicle, PickupInspectExteriorRequest $request)
    {
        $this->authorize('orders inspection');
        $this->authorize('viewAssignedToMe', $order);

        if ($vehicle->order_id !== $order->id) {
            return $this->makeErrorResponse(
                trans('Vehicle not found.'),
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        try {
            return new VehicleResource(
                $this->orderService->inspectPickupExterior(
                    $order,
                    $vehicle,
                    $request->getDto()
                )
            );
        } catch (Exception $e) {
            Log::error($e->getTraceAsString());
            return $this->makeErrorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Delete vehicle photo from inspection
     *
     * @param Order $order
     * @param Vehicle $vehicle
     * @param Request $request
     * @return VehicleResource|JsonResponse
     *
     * @OA\Post(
     *     path="/api/mobile/orders/{orderId}/vehicles/{vehicleId}/delete-pickup-photo",
     *     tags={"Mobile Orders"},
     *     summary="Delete vehicle photo from pickup inspection",
     *     operationId="Delete vehicle photo",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="photo_id", in="query", description="photo id (from 1 to max photos)", required=true,
     *          @OA\Schema(type="number")
     *     ),
     *     @OA\Response(response=204, description="Successful operation"),
     * )
     *
     * @throws AuthorizationException
     * @throws Throwable
     */
    public function deletePickupPhoto(Order $order, Vehicle $vehicle, Request $request)
    {
        $this->authorize('orders inspection');
        $this->authorize('viewAssignedToMe', $order);

        if ($vehicle->order_id !== $order->id) {
            return $this->makeErrorResponse(
                trans('Vehicle not found.'),
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        try {
            return new VehicleResource(
                $this->orderService->deletePickupPhoto(
                    $order,
                    $vehicle,
                    $request->photo_id
                )
            );
        } catch (Exception $e) {
            Log::error($e->getTraceAsString());
            return $this->makeErrorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Interior inspection
     *
     * @param Order $order
     * @param Vehicle $vehicle
     * @param InspectInteriorRequest $request
     * @return VehicleResource|JsonResponse
     *
     * @OA\Post(
     *     path="/api/mobile/orders/{orderId}/vehicles/{vehicleId}/inspect-pickup-interior",
     *     tags={"Mobile Orders"},
     *     summary="Interior pickup inspection",
     *     operationId="Interior inspection",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="condition_dark", in="query", required=false,
     *          @OA\Schema(type="boolean")
     *     ),
     *     @OA\Parameter(name="condition_snow", in="query", required=false,
     *          @OA\Schema(type="boolean")
     *     ),
     *     @OA\Parameter(name="condition_rain", in="query", required=false,
     *          @OA\Schema(type="boolean")
     *     ),
     *     @OA\Parameter(name="condition_dirty_vehicle", in="query", required=false,
     *          @OA\Schema(type="boolean")
     *     ),
     *     @OA\Parameter(name="odometer", in="query", description="REQUIRED ONLY IF notes field not present", required=true,
     *          @OA\Schema(type="number")
     *     ),
     *     @OA\Parameter(name="notes", in="query", description="REQUIRED ONLY IF odometer field not present", required=true,
     *          @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(name="num_keys", in="query", required=false,
     *          @OA\Schema(type="number")
     *     ),
     *     @OA\Parameter(name="num_remotes", in="query", required=false,
     *          @OA\Schema(type="number")
     *     ),
     *     @OA\Parameter(name="num_headrests", in="query", required=false,
     *          @OA\Schema(type="number")
     *     ),
     *     @OA\Parameter(name="drivable", in="query", required=false,
     *          @OA\Schema(type="boolean")
     *     ),
     *     @OA\Parameter(name="windscreen", in="query", required=false,
     *          @OA\Schema(type="boolean")
     *     ),
     *     @OA\Parameter(name="glass_all_intact", in="query", required=false,
     *          @OA\Schema(type="boolean")
     *     ),
     *     @OA\Parameter(name="title", in="query", required=false,
     *          @OA\Schema(type="boolean")
     *     ),
     *     @OA\Parameter(name="cargo_cover", in="query", required=false,
     *          @OA\Schema(type="boolean")
     *     ),
     *     @OA\Parameter(name="spare_tire", in="query", required=false,
     *          @OA\Schema(type="boolean")
     *     ),
     *     @OA\Parameter(name="radio", in="query", required=false,
     *          @OA\Schema(type="boolean")
     *     ),
     *     @OA\Parameter(name="manuals", in="query", required=false,
     *          @OA\Schema(type="boolean")
     *     ),
     *     @OA\Parameter(name="navigation_disk", in="query", required=false,
     *          @OA\Schema(type="boolean")
     *     ),
     *     @OA\Parameter(name="plugin_charger_cable", in="query", required=false,
     *          @OA\Schema(type="boolean")
     *     ),
     *     @OA\Parameter(name="headphones", in="query", required=false,
     *          @OA\Schema(type="boolean")
     *     ),
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/VehicleResource")
     *     ),
     * )
     *
     * @throws AuthorizationException
     * @throws Throwable
     */
    public function inspectPickupInterior(Order $order, Vehicle $vehicle, InspectInteriorRequest $request)
    {
        $this->authorize('orders inspection');
        $this->authorize('viewAssignedToMe', $order);

        if ($vehicle->order_id !== $order->id) {
            return $this->makeErrorResponse(
                trans('Vehicle not found.'),
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        try {
            return new VehicleResource(
                $this->orderService->inspectPickupInterior(
                    $order,
                    $vehicle,
                    $request->validated()
                )
            );
        } catch (Exception $e) {
            Log::error($e->getTraceAsString());
            return $this->makeErrorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Send damage photo during inspection
     *
     * @param Order $order
     * @param Vehicle $vehicle
     * @param InspectDamageRequest $request
     * @return VehicleResource|JsonResponse
     *
     * @OA\Post(
     *     path="/api/mobile/orders/{orderId}/vehicles/{vehicleId}/inspect-delivery-damage",
     *     tags={"Mobile Orders"},
     *     summary="Send damage photo during delivery inspection",
     *     operationId="Send damage photo",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="damage_labels", in="query", required=false,
     *          @OA\Schema(type="array", @OA\Items(type="string"))
     *     ),
     *     @OA\Parameter(name="damage_photo", in="query", required=true,
     *          @OA\Schema(type="file")
     *     ),
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/VehicleResource")
     *     ),
     * )
     */
    public function inspectDeliveryDamage(Order $order, Vehicle $vehicle, InspectDamageRequest $request)
    {
        try {
            return new VehicleResource(
                $this->orderService->inspectDeliveryDamage(
                    $vehicle,
                    $request->dto()
                )
            );
        } catch (Exception $e) {
            Log::error($e->getTraceAsString());
            return $this->makeErrorResponse(null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Send vehicle photo to inspection
     *
     * @param Order $order
     * @param Vehicle $vehicle
     * @param DeliveryInspectExteriorRequest $request
     * @return VehicleResource|JsonResponse
     *
     * @OA\Post(
     *     path="/api/mobile/orders/{orderId}/vehicles/{vehicleId}/inspect-delivery-exterior",
     *     tags={"Mobile Orders"},
     *     summary="Send vehicle photo to delivery inspection",
     *     operationId="Upload vehicle delivery photo",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="photo_id", in="query", description="photo id (from 1 to max photos)", required=true,
     *          @OA\Schema(type="number")
     *     ),
     *     @OA\Parameter(name="inspection_photo", in="query", required=true,
     *          @OA\Schema(type="file")
     *     ),
     *     @OA\Parameter(name="photo_lat", in="query", description="vehicle photo latitude", required=true,
     *          @OA\Schema(type="number")
     *     ),
     *     @OA\Parameter(name="photo_lng", in="query", description="vehicle photo longitude", required=true,
     *          @OA\Schema(type="number")
     *     ),
     *     @OA\Parameter(name="photo_timestamp", in="query", description="vehicle photo timestamp", required=false,
     *          @OA\Schema(type="integer",)
     *     ),
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/VehicleResource")
     *     ),
     * )
     *
     * @throws AuthorizationException
     * @throws Throwable
     */
    public function inspectDeliveryExterior(Order $order, Vehicle $vehicle, DeliveryInspectExteriorRequest $request)
    {
        $this->authorize('orders inspection');
        $this->authorize('viewAssignedToMe', $order);

        if ($vehicle->order_id !== $order->id) {
            return $this->makeErrorResponse(
                trans('Vehicle not found.'),
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        try {
            return new VehicleResource(
                $this->orderService->inspectDeliveryExterior(
                    $order,
                    $vehicle,
                    $request->getDto()
                )
            );
        } catch (Exception $e) {
            Log::error($e->getTraceAsString());

            return $this->makeErrorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Delete vehicle photo from inspection
     *
     * @param Order $order
     * @param Vehicle $vehicle
     * @param Request $request
     * @return VehicleResource|JsonResponse
     *
     * @throws AuthorizationException
     * @throws Throwable
     * @OA\Post(
     *     path="/api/mobile/orders/{orderId}/vehicles/{vehicleId}/delete-delivery-photo",
     *     tags={"Mobile Orders"},
     *     summary="Delete vehicle photo from delivery inspection",
     *     operationId="Delete vehicle photo",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="photo_id", in="query", description="photo id (from 1 to max photos)", required=true,
     *          @OA\Schema(type="number")
     *     ),
     *     @OA\Response(response=204, description="Successful operation"),
     * )
     *
     */
    public function deleteDeliveryPhoto(Order $order, Vehicle $vehicle, Request $request)
    {
        $this->authorize('orders inspection');
        $this->authorize('viewAssignedToMe', $order);

        if ($vehicle->order_id !== $order->id) {
            return $this->makeErrorResponse(
                trans('Vehicle not found.'),
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        try {
            return new VehicleResource(
                $this->orderService->deleteDeliveryPhoto(
                    $order,
                    $vehicle,
                    $request->photo_id
                )
            );
        } catch (Exception $e) {
            Log::error($e->getTraceAsString());
            return $this->makeErrorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Interior inspection
     *
     * @param Order $order
     * @param Vehicle $vehicle
     * @param InspectInteriorRequest $request
     * @return VehicleResource|JsonResponse
     *
     * @OA\Post(
     *     path="/api/mobile/orders/{orderId}/vehicles/{vehicleId}/inspect-delivery-interior",
     *     tags={"Mobile Orders"},
     *     summary="Interior delivery inspection",
     *     operationId="Interior inspection",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="condition_dark", in="query", required=false,
     *          @OA\Schema(type="boolean")
     *     ),
     *     @OA\Parameter(name="condition_snow", in="query", required=false,
     *          @OA\Schema(type="boolean")
     *     ),
     *     @OA\Parameter(name="condition_rain", in="query", required=false,
     *          @OA\Schema(type="boolean")
     *     ),
     *     @OA\Parameter(name="condition_dirty_vehicle", in="query", required=false,
     *          @OA\Schema(type="boolean")
     *     ),
     *     @OA\Parameter(name="odometer", in="query", description="REQUIRED ONLY IF notes field not present", required=true,
     *          @OA\Schema(type="number")
     *     ),
     *     @OA\Parameter(name="notes", in="query",
     *          description="REQUIRED ONLY IF odometer field not present", required=true,
     *          @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(name="num_keys", in="query", required=false,
     *          @OA\Schema(type="number")
     *     ),
     *     @OA\Parameter(name="num_remotes", in="query", required=false,
     *          @OA\Schema(type="number")
     *     ),
     *     @OA\Parameter(name="num_headrests", in="query", required=false,
     *          @OA\Schema(type="number")
     *     ),
     *     @OA\Parameter(name="drivable", in="query", required=false,
     *          @OA\Schema(type="boolean")
     *     ),
     *     @OA\Parameter(name="windscreen", in="query", required=false,
     *          @OA\Schema(type="boolean",)
     *     ),
     *     @OA\Parameter(name="glass_all_intact", in="query", required=false,
     *          @OA\Schema(type="boolean")
     *     ),
     *     @OA\Parameter(name="title", in="query", required=false,
     *          @OA\Schema(type="boolean")
     *     ),
     *     @OA\Parameter(name="cargo_cover", in="query", required=false,
     *          @OA\Schema(type="boolean")
     *     ),
     *     @OA\Parameter(name="spare_tire", in="query", description="",required=false,
     *          @OA\Schema(type="boolean")
     *     ),
     *     @OA\Parameter(name="radio", in="query",required=false,
     *          @OA\Schema(type="boolean")
     *     ),
     *     @OA\Parameter(name="manuals", in="query", required=false,
     *          @OA\Schema(type="boolean",)
     *     ),
     *     @OA\Parameter(name="navigation_disk", in="query", required=false,
     *          @OA\Schema(type="boolean",)
     *     ),
     *     @OA\Parameter(name="plugin_charger_cable", in="query", required=false,
     *          @OA\Schema(type="boolean",)
     *     ),
     *     @OA\Parameter(name="headphones", in="query", required=false,
     *          @OA\Schema(type="boolean",)
     *     ),
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/VehicleResource")
     *     ),
     * )
     *
     * @throws AuthorizationException
     * @throws Throwable
     */
    public function inspectDeliveryInterior(Order $order, Vehicle $vehicle, InspectInteriorRequest $request)
    {
        $this->authorize('orders inspection');
        $this->authorize('viewAssignedToMe', $order);

        if ($vehicle->order_id !== $order->id) {
            return $this->makeErrorResponse(
                trans('Vehicle not found.'),
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        try {
            return new VehicleResource(
                $this->orderService->inspectDeliveryInterior(
                    $order,
                    $vehicle,
                    $request->validated()
                )
            );
        } catch (Exception $e) {
            Log::error($e->getTraceAsString());
            return $this->makeErrorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Send pickup signature
     *
     * @param Order $order
     * @param InspectionSignatureRequest $request
     * @return OrderResource|JsonResponse
     *
     * @OA\Post(
     *     path="/api/mobile/orders/{orderId}/pickup-signature",
     *     tags={"Mobile Orders"},
     *     summary="Send pickup signature",
     *     operationId="Send pickup signature",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="customer_not_available", in="query", description="true if customer not available", required=true,
     *          @OA\Schema(type="boolean",)
     *     ),
     *     @OA\Parameter(name="customer_full_name", in="query", description="customer full name", required=true,
     *          @OA\Schema(type="string",)
     *     ),
     *     @OA\Parameter(name="customer_signature", in="query", description="customer signature photo", required=true,
     *          @OA\Schema(type="file",)
     *     ),
     *     @OA\Parameter(name="driver_signature", in="query", description="driver signature photo", required=true,
     *          @OA\Schema(type="file")
     *     ),
     *     @OA\Parameter(name="actual_date", in="query", description="inspection actual date unix timestamp", required=false,
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
    public function pickupSignature(Order $order, InspectionSignatureRequest $request)
    {
        $this->authorize('orders inspection');
        $this->authorize('viewAssignedToMe', $order);

        try {
            $order = $this->orderService->pickupSignature(
                $order,
                $request->validated(),
                $request->header('TimezoneId')
            );

            $order = $this->statusService()->autoChangeStatus($order);

            return OrderResource::make($order->loadMissingRelations());
        } catch (Exception $e) {
            return $this->makeErrorResponse(null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Send delivery signature
     *
     * @param Order $order
     * @param InspectionSignatureRequest $request
     * @return OrderResource|JsonResponse
     *
     * @OA\Post(
     *     path="/api/mobile/orders/{orderId}/delivery-signature",
     *     tags={"Mobile Orders"},
     *     summary="Send delivery signature",
     *     operationId="Send delivery signature",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="customer_not_available", in="query", description="true if customer not available", required=true,
     *          @OA\Schema(type="boolean")
     *     ),
     *     @OA\Parameter(name="customer_full_name", in="query", description="customer full name", required=true,
     *          @OA\Schema(type="string",)
     *     ),
     *     @OA\Parameter(name="customer_signature", in="query", description="customer signature photo", required=true,
     *          @OA\Schema(type="file")
     *     ),
     *     @OA\Parameter(name="driver_signature", in="query", description="driver signature photo", required=true,
     *          @OA\Schema(type="file")
     *     ),
     *     @OA\Parameter(name="actual_date", in="query", description="inspection actual date unix timestamp", required=false,
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
    public function deliverySignature(Order $order, InspectionSignatureRequest $request)
    {
        $this->authorize('orders inspection');
        $this->authorize('viewAssignedToMe', $order);

        try {
            $order = $this->orderService->deliverySignature(
                $order,
                $request->validated(),
                $request->header('TimezoneId')
            );

            $order = $this->statusService()->autoChangeStatus($order);

            return OrderResource::make($order->loadMissingRelations());
        } catch (Exception $e) {
            return $this->makeErrorResponse(null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Send Invoice pdf file
     *
     * @param Order $order
     * @param SendDocsMobileRequest $request
     * @param GeneratePdfService $generatePdfService
     * @return JsonResponse
     *
     * @throws Throwable
     * @OA\Post(
     *     path="/api/mobile/orders/{orderId}/send-docs",
     *     tags={"Mobile Orders"},
     *     summary="Send Invoice/BOL pdf files",
     *     operationId="Send Invoice/BOL pdf files",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter (
     *          name="orderId",
     *          in="path",
     *          description="Order ID",
     *          required=true,
     *          @OA\Schema (
     *              type="integer"
     *          )
     *     ),
     *     @OA\RequestBody(
     *          @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema (
     *                  type="object",
     *                  required={"recipient_email", "content"},
     *                  @OA\Property (
     *                      property="recipient_email",
     *                      description="Recipient email",
     *                      type="string",
     *                      example="my.email@gmail.com",
     *                      nullable=false
     *                  ),
     *                  @OA\Property (
     *                      property="content",
     *                      description="Selected sending docs",
     *                      type="string",
     *                      nullable=false,
     *                      enum={"invoice", "bol", "both"}
     *                  ),
     *                  @OA\Property (
     *                      property="after_inspection",
     *                      description="Inspection type",
     *                      type="string",
     *                      nullable=true,
     *                      enum={"pickup","delivery"}
     *                  ),
     *             )
     *          )
     *     ),
     *     @OA\Response(response=200, description="Successful operation"),
     * )
     */
    public function sendDocs(Order $order, SendDocsMobileRequest $request, GeneratePdfService $generatePdfService): JsonResponse
    {
        try {
            if ($this->checkAfterInspection($request->afterInspection(), $order)) {
                SendDocsDelay::query()->upsert(
                    [
                        'sender_id' => $request->user()->id,
                        'order_id' => $order->id,
                        'inspection_type' => $request->afterInspection(),
                        'request_data' => json_encode($request->validated(), JSON_THROW_ON_ERROR)
                    ],
                    [
                        'order_id',
                        'inspection_type'
                    ],
                    [
                        'request_data'
                    ]
                );
            } else {
                $generatePdfService->sendDocs($request->user(), $request->dto());
            }

            return $this->makeSuccessResponse(null, Response::HTTP_OK);
        } catch (EmptyInvoiceTotalDue | SenderDoesNotHaveEmail $e) {
            return $this->makeErrorResponse($e->getMessage(), Response::HTTP_FAILED_DEPENDENCY);
        } catch (Exception $e) {
            Log::error($e->getTraceAsString());
            return $this->makeErrorResponse(null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function checkAfterInspection(?string $afterInspection, Order $order): bool
    {
        if (!$afterInspection) {
            return false;
        }

        if ($afterInspection === 'pickup') {
            return !($order->isPickedUp() && $order->has_pickup_inspection && $order->has_pickup_signature);
        }
        //delivery
        return !($order->isDelivered() && $order->has_delivery_inspection && $order->has_delivery_signature);
    }
}
