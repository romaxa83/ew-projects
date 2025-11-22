<?php

namespace App\Http\Controllers\Api\V1\Orders\Parts;

use App\Foundations\Modules\Permission\Permissions as Permission;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Orders\Parts\OrderPartsItemRequest;
use App\Http\Resources\Orders\Parts\ItemResource;
use App\Models\Orders\Parts\Item;
use App\Models\Orders\Parts\Order;
use App\Repositories\Orders\Parts\OrderRepository;
use App\Services\Orders\Parts\ItemService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class ItemController extends ApiController
{
    public function __construct(
        protected ItemService $service,
        protected OrderRepository $orderRepo,
    )
    {}

    /**
     * @OA\Post(
     *     path="/api/v1/orders/parts/{id}/item",
     *     tags={"Parts order"},
     *     security={{"Basic": {}}},
     *     summary="Add item to parts order",
     *     operationId="AddItemToPartsOrder",
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(ref="#/components/parameters/ValidateOnly"),
     *
     *     @OA\Parameter(ref="#/components/parameters/IDPath"),
     *
     *     @OA\RequestBody(required=true,
     *         @OA\JsonContent(ref="#/components/schemas/OrderPartsItemRequest")
     *     ),
     *
     *     @OA\Response(response=200, description="Order parts item data",
     *         @OA\JsonContent(ref="#/components/schemas/OrderPartsItemResource")
     *     ),
     *
     *     @OA\Response(response="422", description="Validation", @OA\JsonContent(ref="#/components/schemas/ValidationErrors")),
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function add(OrderPartsItemRequest $request, $id): ItemResource|JsonResponse
    {
        $this->authorize(Permission\Order\Parts\OrderUpdatePermission::KEY);

        /** @var $order Order */
        $order = $this->orderRepo->getById($id);

        Order::assertSalesManager($order);

        if($order->isPaid()){
            return $this->errorJsonMessage(
                __("exceptions.orders.parts.cant_edit_paid"),
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }
        if(!$order->status->statusForEdit()){
            return $this->errorJsonMessage(
                __("exceptions.orders.parts.cant_edit"),
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        return ItemResource::make(
            $this->service->create(
                $request->getDto(),
                $order,
                !$order->isDraft()
            )
        );
    }

    /**
     * @OA\Post(
     *     path="/api/v1/orders/parts/{id}/item/{itemId}",
     *     tags={"Parts order"},
     *     security={{"Basic": {}}},
     *     summary="Update item to parts order",
     *     operationId="UpdateItemToPartOrder",
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(ref="#/components/parameters/ValidateOnly"),
     *
     *     @OA\Parameter(ref="#/components/parameters/IDPath"),
     *     @OA\Parameter(name="{itemId}", in="path", required=true, description="ID item",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\RequestBody(required=true,
     *         @OA\JsonContent(ref="#/components/schemas/OrderPartsItemRequest")
     *     ),
     *
     *     @OA\Response(response=200, description="Order parts item data",
     *         @OA\JsonContent(ref="#/components/schemas/OrderPartsItemResource")
     *     ),
     *
     *     @OA\Response(response="422", description="Validation", @OA\JsonContent(ref="#/components/schemas/ValidationErrors")),
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function update(OrderPartsItemRequest $request, $id, $itemId): ItemResource|JsonResponse
    {
        $this->authorize(Permission\Order\Parts\OrderUpdatePermission::KEY);

        /** @var $order Order */
        $order = $this->orderRepo->getById($id);

        Order::assertSalesManager($order);

        if($order->isPaid()){
            return $this->errorJsonMessage(
                __("exceptions.orders.parts.cant_edit_paid"),
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }
        if(!$order->status->statusForEdit()){
            return $this->errorJsonMessage(
                __("exceptions.orders.parts.cant_edit"),
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        /** @var $item Item */
        if(!$item = $order->items()->where('id', $itemId)->first()){
            throw new \Exception(__("exceptions.orders.parts.not_found_item"), Response::HTTP_NOT_FOUND);
        }

        return ItemResource::make(
            $this->service->update(
                $item,
                $request->getDto(),
                !$order->isDraft()
            )
        );
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/orders/parts/{id}/item/{itemId}",
     *     tags={"Parts order"},
     *     security={{"Basic": {}}},
     *     summary="Delete item from parts order",
     *     operationId="DeleteItemFromPartOrder",
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *
     *     @OA\Parameter(ref="#/components/parameters/IDPath"),
     *     @OA\Parameter(name="{itemId}", in="path", required=true, description="ID item",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\Response(response=200, description="Order parts item data",
     *         @OA\JsonContent(ref="#/components/schemas/OrderPartsItemListResource")
     *     ),
     *
     *     @OA\Response(response="422", description="Validation", @OA\JsonContent(ref="#/components/schemas/ValidationErrors")),
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function delete($id, $itemId): AnonymousResourceCollection|JsonResponse
    {
        $this->authorize(Permission\Order\Parts\OrderUpdatePermission::KEY);

        /** @var $order Order */
        $order = $this->orderRepo->getById($id);

        Order::assertSalesManager($order);

        if($order->isPaid()){
            return $this->errorJsonMessage(
                __("exceptions.orders.parts.cant_edit_paid"),
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }
        if(!$order->status->statusForEdit()){
            return $this->errorJsonMessage(
                __("exceptions.orders.parts.cant_edit"),
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        /** @var $item Item */
        if(!$item = $order->items()->where('id', $itemId)->first()){
            throw new \Exception(__("exceptions.orders.parts.not_found_item"), Response::HTTP_NOT_FOUND);
        }

        if($order->items->count() < 2){
            return $this->errorJsonMessage(
                __("exceptions.orders.parts.cant_delete_last_item"),
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $this->service->delete($item, !$order->isDraft());

        $order->refresh();

        return ItemResource::collection($order->items);
    }
}
