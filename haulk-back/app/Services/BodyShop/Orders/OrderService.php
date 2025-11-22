<?php

namespace App\Services\BodyShop\Orders;

use App\Dto\BodyShop\Orders\OrderDto;
use App\Dto\BodyShop\Orders\TypeOfWorkDto;
use App\Models\BodyShop\Inventories\Inventory;
use App\Models\BodyShop\Orders\Order;
use App\Models\BodyShop\Orders\OrderComment;
use App\Models\BodyShop\Orders\Payment;
use App\Models\BodyShop\Orders\TypeOfWork;
use App\Models\History\History;
use App\Models\Users\User;
use App\Services\BodyShop\Inventories\InventoryService;
use App\Services\BodyShop\TypesOfWork\TypeOfWorkService;
use App\Services\Events\BodyShop\Order\OrderEventService;
use App\Services\Events\EventService;
use DB;
use Log;
use Exception;
use Illuminate\Http\UploadedFile;

class OrderService
{
    private ?User $user = null;

    private TypeOfWorkService $typeOfWorkService;

    private InventoryService $inventoryService;

    public function __construct(TypeOfWorkService $typesOfWorkService, InventoryService $inventoryService)
    {
        $this->typeOfWorkService = $typesOfWorkService;
        $this->inventoryService = $inventoryService;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;
        $this->inventoryService->setUser($user);

        return $this;
    }

    public function create(OrderDto $dto): Order
    {
        try {
            DB::beginTransaction();

            $additionalData = [
                'order_number' => $this->generateOrderNumber(),
                'status_changed_at' => now(),
                'status' => Order::STATUS_NEW,
                'is_billed' => false,
            ];

            /** @var Order $order */
            $order = Order::query()->make($dto->getOrderData() + $additionalData);
            $order->saveOrFail();

            /** @var TypeOfWorkDto $work */
            foreach ($dto->getTypeOfWorkData() as $work) {
                /** @var TypeOfWork $typeOfWork */
                $typeOfWork = $order->typesOfWork()->create($work->getTypeOfWorkData());
                foreach ($work->getInventoriesData() as $inventory) {
                    /** @var Inventory $inventoryItem */
                    $inventoryItem = Inventory::find($inventory['id']);
                    $typeOfWork->inventories()->create([
                        'inventory_id' => $inventory['id'],
                        'quantity' => $inventory['quantity'],
                        'price' => $inventoryItem->price_retail
                    ]);
                    $this->inventoryService->reserveForOrder($order, $inventoryItem, $inventory['quantity']);
                }

                if ($work->isSaveToTheList()) {
                    $this->typeOfWorkService->create($work);
                }
            }

            $this->addAttachments($order, $dto->getAttachments());

            $order->setAmounts();

            EventService::bsOrder($order)
                ->user($this->user)
                ->create();

            DB::commit();

            return $order;
        } catch (Exception $exception) {
            DB::rollBack();

            throw $exception;
        }
    }

    public function update(Order $order, OrderDto $dto, bool $withInventoryCounting = true): Order
    {
        try {
            DB::beginTransaction();

            $event = EventService::bsOrder($order);

            $order->update($dto->getOrderData());
            $updatedTypesOfWork = [];

            /** @var TypeOfWorkDto $work */
            foreach ($dto->getTypeOfWorkData() as $work) {
                /** @var TypeOfWork $typeOfWork */
                if ($work->getTypeOfWorkData()['id'] ?? null) {
                    $typeOfWork = $order->typesOfWork()->find($work->getTypeOfWorkData()['id']);
                    $typeOfWork->update($work->getTypeOfWorkData());
                } else {
                    $typeOfWork = $order->typesOfWork()->create($work->getTypeOfWorkData());
                }
                $updatedTypesOfWork[] = $typeOfWork->id;

                $updatedInventories = [];
                foreach ($work->getInventoriesData() as $inventory) {
                    /** @var Inventory $inventoryItem */
                    $inventoryItem = Inventory::find($inventory['id']);

                    $typeOfWorkInventory = $typeOfWork->inventories()
                        ->where('inventory_id', $inventory['id'])
                        ->first();

                    if ($typeOfWorkInventory) {
                        $data = [
                            'quantity' => $inventory['quantity'],
                        ];

                        if ($dto->isNeedToUpdatePrices()) {
                            $data['price'] = $inventoryItem->price_retail;
                            if ($withInventoryCounting && $inventoryItem->price_retail !== $typeOfWorkInventory->price) {
                                $this->inventoryService->changeReservedPriceForOrder($order, $inventoryItem);
                            }
                        }

                        if ($withInventoryCounting) {
                            $this->inventoryService->changeReservedQuantityForOrder(
                                $order,
                                $inventoryItem,
                                $inventory['quantity'],
                                $typeOfWorkInventory->quantity,
                                $typeOfWorkInventory->price
                            );
                        }

                        $typeOfWorkInventory->update($data);
                    } else {
                        $typeOfWorkInventory = $typeOfWork->inventories()->create([
                            'inventory_id' => $inventory['id'],
                            'quantity' => $inventory['quantity'],
                            'price' => $inventoryItem->price_retail,
                        ]);

                        if ($withInventoryCounting) {
                            $this->inventoryService->reserveForOrder(
                                $order,
                                $inventoryItem,
                                $inventory['quantity']
                            );
                        }

                    }

                    $updatedInventories[] = $typeOfWorkInventory->id;
                }

                //delete inventories absent in query
                foreach ($typeOfWork->inventories as $inventoryItem) {
                    if (!in_array($inventoryItem->id, $updatedInventories)) {
                        $inventoryItem->delete();

                        if ($withInventoryCounting) {
                            $this->inventoryService->reduceReservedInOrder(
                                $order,
                                $inventoryItem->inventory,
                                $inventoryItem->quantity,
                                $inventoryItem->price
                            );
                        }
                    }
                }

                if ($work->isSaveToTheList()) {
                    $this->typeOfWorkService->create($work);
                }
            }

            //delete types of work absent in query
            foreach ($order->typesOfWork as $typeOfWork) {
                if (!in_array($typeOfWork->id, $updatedTypesOfWork)) {
                    foreach ($typeOfWork->inventories as $inventoryItem) {
                        if ($withInventoryCounting && !in_array($inventoryItem->id, $updatedInventories)) {
                            $this->inventoryService->reduceReservedInOrder(
                                $order,
                                $inventoryItem->inventory,
                                $inventoryItem->quantity,
                                $inventoryItem->price
                            );
                        }
                    }
                    $typeOfWork->delete();
                }
            }

            $this->addAttachments($order, $dto->getAttachments());

            $order->setAmounts();
            $order->resolvePaidStatus();

            if ($order->is_billed) {
                $order->markAsNotBilled();
            }

            $event->user($this->user)
                 ->update();

            DB::commit();

            return $order;
        } catch (Exception $exception) {
            DB::rollBack();

            throw $exception;
        }
    }

    private function generateOrderNumber(): string
    {
        $lastOrder = Order::query()
            ->withTrashed()
            ->select('order_number')
            ->where('created_at', '>=', now()->startOfDay())
            ->orderBy('id', 'desc')
            ->first();

        $lastNumber = 0;

        if ($lastOrder) {
            $data = explode('-', $lastOrder->order_number);
            $lastNumber = end($data);
        }

        return date('mdY-') . ($lastNumber + 1);
    }

    public function addAttachments(Order $order, array $attachments = []): void
    {
        foreach ($attachments as $attachment) {
            $this->addAttachment($order, $attachment);
        }
    }

    public function addAttachment(Order $order, UploadedFile $file, bool $triggerEvent = false): Order
    {
        try {
            $event = EventService::bsOrder($order);

            $order->addMediaWithRandomName(Order::ATTACHMENT_COLLECTION_NAME, $file);

            if ($triggerEvent) {
                $event->user($this->user)
                    ->update(OrderEventService::ACTION_ATTACHED_DOCUMENT);
            }

            return $order;
        } catch (Exception $e) {
            Log::error($e);
            throw $e;
        }
    }

    public function deleteAttachment(Order $order, int $mediaId = 0): void
    {
        if ($order->media->find($mediaId)) {

            $event = EventService::bsOrder($order);

            $order->deleteMedia($mediaId);

            $event->user($this->user)
                ->update(OrderEventService::ACTION_DELETE_ATTACHMENT);

            return;
        }

        throw new Exception(trans('File not found.'));
    }

    public function history(int $order_id)
    {
        $history = History::query()
            ->where(
                [
                    ['model_id', $order_id],
                    ['model_type', Order::class],
                ]
            )
            ->latest('performed_at')
            ->get();

        if ($history) {
            foreach ($history as &$h) {
                if (isset($h['meta']) && is_array($h['meta'])) {
                    $h['message'] = trans($h['message'], $h['meta']);
                }
            }
        }

        return $history;
    }

    public function addComment(Order $order, array $attributes): OrderComment
    {
        try {
            DB::beginTransaction();

            $event = EventService::bsComment($order)
                ->user($this->user);

            $comment = new OrderComment($attributes);
            $comment->user_id = $this->user->id;

            $order->comments()->save($comment);

            $event->create();

            DB::commit();

            return $comment;
        } catch (Exception $e) {
            DB::rollBack();

            throw $e;
        }
    }

    public function deleteComment(Order $order, OrderComment $comment): void
    {
        try {
            DB::beginTransaction();

            $event = EventService::bsComment($order, $comment)
                ->user($this->user);

            $comment->delete();

            $event->delete();

            DB::commit();

            return;
        } catch (Exception $e) {
            DB::rollBack();

            throw $e;
        }
    }

    public function changeStatus(Order $order, string $newStatus): Order
    {
        try {
            DB::beginTransaction();
            $event = EventService::bsOrder($order);
            $oldStatus = $order->status;

            $order->changeStatus($newStatus);

            $event->user($this->user)
                ->update(OrderEventService::ACTION_STATUS_CHANGED);

            if (
                $oldStatus === Order::STATUS_FINISHED
                && in_array($newStatus, [Order::STATUS_NEW, Order::STATUS_IN_PROCESS])
            ) {
                foreach ($order->inventories as $inventory) {
                    $this->inventoryService->reserveOnMovingOrderFromFinished(
                        $order,
                        $inventory->inventory,
                        $inventory->price
                    );
                }

                $order->update([
                    'parts_cost' => null,
                    'profit' => null
                ]);
            }

            if (
                $newStatus === Order::STATUS_FINISHED
                && in_array($oldStatus, [Order::STATUS_NEW, Order::STATUS_IN_PROCESS])
            ) {
                foreach ($order->inventories as $inventory) {
                    $this->inventoryService->finishedOrderWithInventory(
                        $order,
                        $inventory->inventory,
                        $inventory->price,
                        $inventory->quantity
                    );
                }

                $partsCost = round($order->getPartsCost(), 2);
                $order->update([
                    'parts_cost' => $partsCost,
                    'profit' => $partsCost
                        ? round($order->total_amount - $partsCost, 2)
                        : $order->total_amount
                ]);
            }

            DB::commit();

            return $order;
        } catch (Exception $e) {
            DB::rollBack();

            throw $e;
        }
    }

    public function reassignMechanic(Order $order, int $mechanicId): Order
    {
        try {
            DB::beginTransaction();

            $event = EventService::bsOrder($order);

            $order->reassignMechanic($mechanicId);

            $event->user($this->user)
                ->update(OrderEventService::ACTION_REASSIGNED_MECHANIC);

            DB::commit();

            return $order;
        } catch (Exception $e) {
            DB::rollBack();

            throw $e;
        }
    }

    public function deleteOrder(Order $order): void
    {
        try {
            DB::beginTransaction();
            foreach ($order->inventories as $inventory) {
                $this->inventoryService->reduceReservedInOrder(
                    $order,
                    $inventory->inventory,
                    $inventory->quantity,
                    $inventory->price,
                    true
                );
            }

            $order->delete();

            EventService::bsOrder($order)
                ->user($this->user)
                ->delete();

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            throw $e;
        }
    }

    public function restoreOrder(Order $order): Order
    {
        try {
            DB::beginTransaction();
            foreach ($order->inventories as $inventory) {
                $this->inventoryService->reserveForOrder(
                    $order,
                    $inventory->inventory,
                    $inventory->quantity,
                    $inventory->price
                );
            }

            $order->restoreOrder();

            EventService::bsOrder($order)
                ->user($this->user)
                ->restore();

            DB::commit();

            return $order;
        } catch (Exception $e) {
            DB::rollBack();

            throw $e;
        }
    }

    public function restoreOrderWithEditing(Order $order, OrderDto $dto): Order
    {
        try {
            DB::beginTransaction();

            $this->update($order, $dto, false);

            foreach ($order->inventories as $inventory) {
                $this->inventoryService->reserveForOrder(
                    $order,
                    $inventory->inventory,
                    $inventory->quantity,
                    $inventory->price
                );
            }

            $order->restoreOrder();

            EventService::bsOrder($order)
                ->user($this->user)
                ->restore();

            DB::commit();

            return $order;
        } catch (Exception $e) {
            DB::rollBack();

            throw $e;
        }
    }

    public function deleteOrderPermanently(Order $order): void
    {
        try {
            DB::beginTransaction();

            $order->forceDelete();

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            Log::error($e);

            throw $e;
        }
    }

    public function createPayment(Order $order, array $paymentData): Payment
    {
        try {
            DB::beginTransaction();

            $event = EventService::bsOrder($order)
                ->user($this->user);

            $payment = $order->payments()->create([
                'amount' => $paymentData['amount'],
                'payment_method' => $paymentData['payment_method'],
                'payment_date' => $paymentData['payment_date'],
                'notes' => $paymentData['notes'] ?? null,
                'reference_number' => $paymentData['reference_number'] ?? null,
            ]);

            $order->refresh();
            $order->setAmounts();
            $order->resolvePaidStatus();

            $event->update(OrderEventService::ACTION_CREATE_PAYMENT);

            DB::commit();

            return $payment;
        } catch (Exception $e) {
            DB::rollBack();

            Log::error($e);

            throw $e;
        }
    }

    public function deletePayment(Order $order, Payment $payment): Payment
    {
        try {
            DB::beginTransaction();

            $event = EventService::bsOrder($order)
                ->user($this->user);

            $payment->delete();

            $order->refresh();
            $order->setAmounts();
            $order->resolvePaidStatus();

            $event->update(OrderEventService::ACTION_DELETE_PAYMENT);

            DB::commit();

            return $payment;
        } catch (Exception $e) {
            DB::rollBack();

            Log::error($e);

            throw $e;
        }
    }
}
