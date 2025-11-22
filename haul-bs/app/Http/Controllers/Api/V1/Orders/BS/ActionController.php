<?php

namespace App\Http\Controllers\Api\V1\Orders\BS;

use App\Foundations\Modules\Permission\Permissions as Permission;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Orders\BS\OrderChangeStatusRequest;
use App\Http\Requests\Orders\BS\OrderReassignMechanicRequest;
use App\Http\Requests\Orders\BS\OrderRequest;
use App\Http\Requests\Orders\BS\OrderRestoreRequest;
use App\Http\Resources\Orders\BS\OrderResource;
use App\Models\Orders\BS\Order;
use App\Models\Users\User;
use App\Repositories\Orders\BS\OrderRepository;
use App\Repositories\Users\UserRepository;
use App\Services\Orders\BS\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class ActionController extends ApiController
{
    public function __construct(
        protected OrderRepository $repo,
        protected UserRepository $userRepository,
        protected OrderService $service,
    )
    {}

    /**
     * @OA\Post(
     *     path="/api/v1/orders/bs/{id}/reassign-mechanic",
     *     tags={"BS order"},
     *     security={{"Basic": {}}},
     *     summary="Reassign mechanic for bodyshop order",
     *     operationId="ReassignMechanicForBS",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/IDPath"),
     *
     *     @OA\RequestBody(required=true,
     *         @OA\JsonContent(ref="#/components/schemas/OrderReassignMechanicRequest")
     *     ),
     *
     *     @OA\Response(response=200, description="Order bodyshop data",
     *         @OA\JsonContent(ref="#/components/schemas/OrderBSResource")
     *     ),
     *
     *     @OA\Response(response="422", description="Validation", @OA\JsonContent(ref="#/components/schemas/ValidationErrors")),
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function reassignMechanic(OrderReassignMechanicRequest $request, $id): OrderResource
    {
        $this->authorize(Permission\Order\BS\OrderReassignMechanicPermission::KEY);

        /** @var $model Order */
        $model = $this->repo->getBy(
            ['id' => $id],
            withException: true,
            exceptionMessage: __("exceptions.orders.bs.not_found"),
        );

        /** @var $mechanic User */
        $mechanic = $this->userRepository->getBy(
            ['id' => $request['mechanic_id']],
        );

        return OrderResource::make(
            $this->service->reassignMechanic($model, $mechanic)
        );
    }

    /**
     * @OA\Post(
     *     path="/api/v1/orders/bs/{id}/change-status",
     *     tags={"BS order"},
     *     security={{"Basic": {}}},
     *     summary="Change status for bodyshop order",
     *     operationId="ChangeStatusForBS",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/IDPath"),
     *
     *     @OA\RequestBody(required=true,
     *         @OA\JsonContent(ref="#/components/schemas/OrderChangeStatusRequest")
     *     ),
     *
     *     @OA\Response(response=200, description="Order bodyshop data",
     *         @OA\JsonContent(ref="#/components/schemas/OrderBSResource")
     *     ),
     *
     *     @OA\Response(response="422", description="Validation", @OA\JsonContent(ref="#/components/schemas/ValidationErrors")),
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function changeStatus(OrderChangeStatusRequest $request): OrderResource
    {
        $this->authorize(Permission\Order\BS\OrderChangeStatusPermission::KEY);

        return OrderResource::make(
            $this->service->changeStatus($request->getOrder(), $request['status'])
        );
    }

    /**
     * @OA\Post(
     *     path="/api/v1/orders/bs/{id}/restore",
     *     tags={"BS order"},
     *     security={{"Basic": {}}},
     *     summary="Restore deleted order",
     *     operationId="RestoreDeletedOrderBS",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/IDPath"),
     *
     *     @OA\Response(response=200, description="Order bodyshop data",
     *         @OA\JsonContent(ref="#/components/schemas/OrderBSResource")
     *     ),
     *
     *     @OA\Response(response="422", description="Validation", @OA\JsonContent(ref="#/components/schemas/ValidationErrors")),
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function restore(OrderRestoreRequest $request): OrderResource
    {
        $this->authorize(Permission\Order\BS\OrderRestorePermission::KEY);

        return OrderResource::make(
            $this->service->restore($request->getOrder())
        );
    }

    /**
     * @OA\Post(
     *     path="/api/v1/orders/bs/{id}/restore-with-editing",
     *     tags={"BS order"},
     *     security={{"Basic": {}}},
     *     summary="Restore with editing order",
     *     operationId="RestoreWithEditingOrderBS",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/IDPath"),
     *
     *     @OA\RequestBody(required=true,
     *         @OA\JsonContent(ref="#/components/schemas/OrderRequest")
     *     ),
     *
     *     @OA\Response(response=200, description="Order bodyshop data",
     *         @OA\JsonContent(ref="#/components/schemas/OrderRequest")
     *     ),
     *
     *     @OA\Response(response="422", description="Validation", @OA\JsonContent(ref="#/components/schemas/ValidationErrors")),
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function restoreWithEditing(OrderRequest $request, $id): OrderResource
    {
        $this->authorize(Permission\Order\BS\OrderRestorePermission::KEY);

        /** @var $model Order */
        $model = $this->repo->getBy(
            ['id' => $id],
            withException: true,
            exceptionMessage: __("exceptions.orders.bs.not_found"),
            withTrashed: true
        );

        return OrderResource::make(
            $this->service->restoreWithEdit($model, $request->getDto())
        );
    }

    /**
     * @OA\Get(
     *     path="/api/v1/orders/bs/{id}/view-deleted",
     *     tags={"BS order"},
     *     security={{"Basic": {}}},
     *     summary="View deleted order",
     *     operationId="ViewDeletedOrderBS",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/IDPath"),
     *
     *     @OA\Response(response=200, description="Order bodyshop data",
     *         @OA\JsonContent(ref="#/components/schemas/OrderBSResource")
     *     ),
     *
     *     @OA\Response(response="422", description="Validation", @OA\JsonContent(ref="#/components/schemas/ValidationErrors")),
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function viewDeleted($id): OrderResource
    {
        $this->authorize(Permission\Order\BS\OrderRestorePermission::KEY);

        /** @var $model Order */
        $model = $this->repo->getBy(
            ['id' => $id],
            withException: true,
            exceptionMessage: __("exceptions.orders.bs.not_found"),
            withTrashed: true
        );

        return OrderResource::make($model);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/orders/bs/{id}/permanently",
     *     tags={"BS order"},
     *     security={{"Basic": {}}},
     *     summary="Delete order permanently",
     *     operationId="DeleteOrderBSPermanently",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/IDPath"),
     *
     *     @OA\Response(response=204, description="Successful delete"),
     *
     *     @OA\Response(response="422", description="Validation", @OA\JsonContent(ref="#/components/schemas/ValidationErrors")),
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function forceDelete($id): JsonResponse
    {
        $this->authorize(Permission\Order\BS\OrderDeletePermanentlyPermission::KEY);

        /** @var $model Order */
        $model = $this->repo->getBy(
            ['id' => $id],
            withException: true,
            exceptionMessage: __("exceptions.orders.bs.not_found"),
            withTrashed: true
        );

        $this->service->forceDelete($model);

        return $this->successJsonMessage(null, Response::HTTP_NO_CONTENT);
    }
}
