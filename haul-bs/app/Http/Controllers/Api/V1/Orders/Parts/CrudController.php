<?php

namespace App\Http\Controllers\Api\V1\Orders\Parts;

use App\Foundations\Modules\Permission\Permissions as Permission;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Orders\Parts\OrderFilterRequest;
use App\Http\Requests\Orders\Parts\OrderPartsRequest;
use App\Http\Resources\Orders\Parts\OrderPaginationResource;
use App\Http\Resources\Orders\Parts\OrderResource;
use App\Models\Orders\Parts\Order;
use App\Models\Users\User;
use App\Services\Orders\Parts\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Response;

class CrudController extends ApiController
{
    public function __construct(
        protected OrderService $service,
    )
    {}

    /**
     * @OA\Get(
     *     path="/api/v1/orders/parts",
     *     tags={"Parts order"},
     *     security={{"Basic": {}}},
     *     summary="Get parts orders pagination",
     *     operationId="GetPartsOrdersPagination",
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
     *         description="Scope for filter by name",
     *         @OA\Schema(type="string", default="null",)
     *     ),
     *     @OA\Parameter(name="search_inventory", in="query", required=false,
     *          description="Scope for filter by name and stock number inventory into a order",
     *          @OA\Schema(type="string", default="null",)
     *      ),
     *     @OA\Parameter(name="search_customer", in="query", description="Customer search by name or email", required=false,
     *         @OA\Schema( type="string")
     *     ),
     *     @OA\Parameter(name="status", in="query", required=false,
     *         description="Order status, (can be get here - /api/v1/orders/parts/catalog/order-statuses)",
     *         @OA\Schema( type="string", enum={"new", "in_process", "sent", "pending_pickup", "delivered", "canceled", "returned", "lost", "damaged"})
     *     ),
     *     @OA\Parameter(name="payment_status", in="query", required=false,
     *         description="Order payment status,(can be get here - /api/v1/orders/parts/catalog/payment-statuses)",
     *         @OA\Schema( type="string", enum={"paid","not_paid", "refunded"})
     *     ),
     *     @OA\Parameter(name="source", in="query", required=false,
     *         description="Order source, (can be get here - /api/v1/orders/parts/catalog/sources)",
     *         @OA\Schema( type="string", enum={"bs", "amazon", "haulk_depot"})
     *     ),
     *     @OA\Parameter(name="date_from", in="query", description="Order created date from", required=false,
     *         @OA\Schema( type="string", default="2023-02-13 10:00",)
     *     ),
     *     @OA\Parameter(name="date_to", in="query", description="Order created date to", required=false,
     *         @OA\Schema( type="string", default="2023-02-13 10:00",)
     *     ),
     *     @OA\Parameter(name="sales_manager_id", in="query", description="Filter by sales manager, only admin", required=false,
     *         @OA\Schema( type="string", default="1",)
     *     ),
     *
     *     @OA\Response(response=200, description="Order parts data",
     *         @OA\JsonContent(ref="#/components/schemas/OrderPartsPaginationResource")
     *     ),
     *
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function index(OrderFilterRequest $request): ResourceCollection
    {
        $this->authorize(Permission\Order\Parts\OrderReadPermission::KEY);

        /** @var $user User */
        $user = $request->user();
        $filter = $request->validated();
        if($user->role->isSalesManager()){
            if(
                isset($filter['sales_manager_id'])
                && $filter['sales_manager_id'] == $user->id
            ){
                $filter['sales_manager_id'] == $user->id;
            } else {
                $filter['for_sales_manager'] = $user->id;
                unset($filter['sales_manager_id']);
            }
        }

        return OrderPaginationResource::collection(
            $this->service->repo->getCustomPagination($filter)
        );
    }

    /**
     * @OA\Post(
     *     path="/api/v1/orders/parts",
     *     tags={"Parts order"},
     *     security={{"Basic": {}}},
     *     summary="Create empty parts order",
     *     operationId="CreatePartsOrder",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(ref="#/components/parameters/ValidateOnly"),
     *
     *     @OA\Response(response=200, description="Order parts data",
     *         @OA\JsonContent(ref="#/components/schemas/OrderPartsResource")
     *     ),
     *
     *     @OA\Response(response="422", description="Validation", @OA\JsonContent(ref="#/components/schemas/ValidationErrors")),
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function store(): OrderResource|JsonResponse
    {
        $this->authorize(Permission\Order\Parts\OrderCreatePermission::KEY);

        return OrderResource::make(
            $this->service->create(auth_user())
        );
    }

    /**
     * @OA\Get(
     *     path="/api/v1/orders/parts/{id}",
     *     tags={"Parts order"},
     *     security={{"Basic": {}}},
     *     summary="Get info about parts order",
     *     operationId="GetInfoAboutPartsOrder",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *
     *     @OA\Parameter(ref="#/components/parameters/IDPath"),
     *
     *     @OA\Response(response=200, description="Order parts data",
     *         @OA\JsonContent(ref="#/components/schemas/OrderPartsResource")
     *     ),
     *
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="404", description="Not Found", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function show($id): OrderResource
    {
        $this->authorize(Permission\Order\Parts\OrderReadPermission::KEY);

        $model = $this->service->repo->getById($id);

        Order::assertSalesManager($model);

        return OrderResource::make($model);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/orders/parts/{id}",
     *     tags={"Parts order"},
     *     security={{"Basic": {}}},
     *     summary="Update parts order",
     *     operationId="UpdatePartsOrder",
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
     *         @OA\JsonContent(ref="#/components/schemas/OrderPartsRequest")
     *     ),
     *
     *     @OA\Response(response=200, description="Order parts data",
     *         @OA\JsonContent(ref="#/components/schemas/OrderPartsResource")
     *     ),
     *
     *     @OA\Response(response="422", description="Validation", @OA\JsonContent(ref="#/components/schemas/ValidationErrors")),
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function update(OrderPartsRequest $request): OrderResource|JsonResponse
    {
        $this->authorize(Permission\Order\Parts\OrderUpdatePermission::KEY);

        /** @var $model Order */
        $model = $request->getOrder();

        Order::assertSalesManager($model);

        // заказ можно редактировать если он оплачен, но не все поля
//        if($model->isPaid()){
//            return $this->errorJsonMessage(
//                __("exceptions.orders.parts.cant_edit_paid"),
//                Response::HTTP_UNPROCESSABLE_ENTITY
//            );
//        }
        if(!$model->status->statusForEdit()){
            return $this->errorJsonMessage(
                __("exceptions.orders.parts.cant_edit"),
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        return OrderResource::make(
            $this->service->update(
                $model,
                $request->getDto(),
                !$model->isDraft()
            )
        );
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/orders/parts/{id}",
     *     tags={"Parts order"},
     *     security={{"Basic": {}}},
     *     summary="Delete parts order",
     *     operationId="DeletePartsOrder",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *
     *     @OA\Parameter(ref="#/components/parameters/IDPath"),
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
        /** @var $model Order */
        $model = $this->service->repo->getById($id);

        Order::assertSalesManager($model);

        if($model->isDraft()){
            $this->authorize(Permission\Order\Parts\OrderDraftDeletePermission::KEY);
        } else {
            $this->authorize(Permission\Order\Parts\OrderDeletePermission::KEY);
        }

        if($model->isPaid()){
            return $this->errorJsonMessage(
                __("exceptions.orders.parts.cant_delete_paid"),
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }
        if(!$model->status->statusForEdit()){
            return $this->errorJsonMessage(
                __("exceptions.orders.parts.cant_delete"),
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $this->service->delete($model);

        return $this->successJsonMessage(null, Response::HTTP_NO_CONTENT);
    }
}
