<?php

namespace App\Services\Orders\BS;

use App\Dto\Orders\BS\OrderDto;
use App\Dto\Orders\BS\OrderTypeOfWorkDto;
use App\Dto\Orders\BS\OrderTypeOfWorkInventoryDto;
use App\Enums\Orders\BS\OrderStatus;
use App\Foundations\Modules\History\Services\OrderBSHistoryService;
use App\Models\Inventories\Inventory;
use App\Models\Orders\BS\Order;
use App\Models\Orders\BS\TypeOfWork;
use App\Models\Orders\BS\TypeOfWorkInventory;
use App\Models\Users\User;
use App\Models\Vehicles\Trailer;
use App\Models\Vehicles\Truck;
use App\Repositories\Orders\BS\OrderRepository;
use App\Services\Events\EventService;
use App\Services\Inventories\InventoryService;
use App\Services\TypeOfWorks\TypeOfWorkService;
use Carbon\CarbonImmutable;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class OrderService
{
    public function __construct(
        protected OrderRepository $repo,
        protected TypeOfWorkService $typeOfWorkService,
        protected InventoryService $inventoryService
    )
    {}

    public function create(OrderDto $dto): Order
    {
        return make_transaction(function () use ($dto){
            $model = $this->fill(new Order(), $dto);
            $model->order_number = $this->generateOrderNumber();
            $model->is_billed = false;
            $model = $this->setStatus($model, OrderStatus::New->value);

            $model->save();

            $this->addAttachments($model, $dto->files);

            foreach ($dto->typeOfWorks as $workDto) {
                /** @var OrderTypeOfWorkDto $workDto */
                $typeOfWork = new TypeOfWork();
                $typeOfWork->name = $workDto->name;
                $typeOfWork->duration = $workDto->duration;
                $typeOfWork->hourly_rate = $workDto->hourlyRate;
                $typeOfWork->order_id = $model->id;
                $typeOfWork->save();

                foreach ($workDto->inventories as $workInventoryDto){
                    /** @var OrderTypeOfWorkInventoryDto $workInventoryDto */

                    /** @var Inventory $inventory */
                    $inventory = Inventory::find($workInventoryDto->inventoryId);

                    $workInventory = new TypeOfWorkInventory();
                    $workInventory->type_of_work_id = $typeOfWork->id;
                    $workInventory->inventory_id = $inventory->id;
                    $workInventory->quantity = $workInventoryDto->quantity;
                    $workInventory->price = $inventory->price_retail;
                    $workInventory->save();

                    $this->inventoryService->reserveForOrder($model, $inventory, $workInventoryDto->quantity);
                }

                if ($workDto->saveToTheList) {
                    $this->typeOfWorkService->create($workDto);
                }
            }

            $model->setAmounts();

            EventService::bsOrder($model)
                ->create()
                ->initiator(auth_user())
                ->setHistory()
                ->exec()
            ;

            return $model;
        });
    }

    public function update(Order $model, OrderDto $dto, bool $withInventoryCounting = true): Order
    {
        return make_transaction(function () use ($model, $dto, $withInventoryCounting){
            $old = $model->dataForUpdateHistory();

            $model = $this->fill($model, $dto);

            $model->save();

            $updatedTypesOfWork = [];

            foreach ($dto->typeOfWorks as $workDto) {
                /** @var $workDto OrderTypeOfWorkDto */
                /** @var $typeOfWork TypeOfWork */
                // создаем или обновляем типы работ заказа
                if($workDto->id){
                    $typeOfWork = $model->typesOfWork()->find($workDto->id);
                } else {
                    $typeOfWork = new TypeOfWork();
                    $typeOfWork->order_id = $model->id;
                }

                $typeOfWork->name = $workDto->name;
                $typeOfWork->duration = $workDto->duration;
                $typeOfWork->hourly_rate = $workDto->hourlyRate;
                $typeOfWork->save();

                $updatedTypesOfWork[] = $typeOfWork->id;

                $updatedInventories = [];
                // обновляем или создаем детали которые участвуют в работах
                foreach ($workDto->inventories as $inventoryDto) {
                    /** @var $inventoryDto OrderTypeOfWorkInventoryDto */
                    /** @var Inventory $inventory */
                    $inventory = Inventory::find($inventoryDto->inventoryId);

                    /** @var $typeOfWorkInventory TypeOfWorkInventory */
                    $typeOfWorkInventory = $typeOfWork->inventories()
                        ->where('inventory_id', $inventoryDto->inventoryId)
                        ->first();

                    if ($typeOfWorkInventory) {

                        if ($dto->needToUpdatePrices) {
                            if ($withInventoryCounting && $inventory->price_retail != $typeOfWorkInventory->price) {
                                $this->inventoryService->changeReservedPriceForOrder($model, $inventory);
                            }

                            $typeOfWorkInventory->price = $inventory->price_retail;
                        }

                        if ($withInventoryCounting) {

                            $this->inventoryService->changeReservedQuantityForOrder(
                                $model,
                                $inventory,
                                $inventoryDto->quantity,
                                $typeOfWorkInventory->quantity,
                                $typeOfWorkInventory->price
                            );
                        }

                        $typeOfWorkInventory->quantity = $inventoryDto->quantity;
                        $typeOfWorkInventory->save();

                    } else {
                        $typeOfWorkInventory = new TypeOfWorkInventory();
                        $typeOfWorkInventory->quantity = $inventoryDto->quantity;
                        $typeOfWorkInventory->inventory_id = $inventoryDto->inventoryId;
                        $typeOfWorkInventory->type_of_work_id = $typeOfWork->id;
                        $typeOfWorkInventory->price = $inventory->price_retail;
                        $typeOfWorkInventory->save();

                        if ($withInventoryCounting) {
                            $this->inventoryService->reserveForOrder(
                                $model,
                                $inventory,
                                $inventoryDto->quantity
                            );
                        }
                    }

                    $updatedInventories[] = $typeOfWorkInventory->id;
                }

                // delete inventories absent in query
                foreach ($typeOfWork->inventories as $inventoryItem) {
                    if (!in_array($inventoryItem->id, $updatedInventories)) {
                        $inventoryItem->delete();

                        if ($withInventoryCounting) {
                            $this->inventoryService->reduceReservedInOrder(
                                $model,
                                $inventoryItem->inventory,
                                $inventoryItem->quantity,
                                $inventoryItem->price
                            );
                        }
                    }
                }

                if ($workDto->saveToTheList) {
                    $this->typeOfWorkService->create($workDto);
                }
            }

            //delete types of work absent in query
            foreach ($model->typesOfWork as $typeOfWork) {
                if (!in_array($typeOfWork->id, $updatedTypesOfWork)) {
                    foreach ($typeOfWork->inventories as $inventoryItem) {
                        if ($withInventoryCounting && !in_array($inventoryItem->id, $updatedInventories)) {
                            $this->inventoryService->reduceReservedInOrder(
                                $model,
                                $inventoryItem->inventory,
                                $inventoryItem->quantity,
                                $inventoryItem->price
                            );
                        }
                    }
                    $typeOfWork->delete();
                }
            }

            $this->addAttachments($model, $dto->files);

            $changed = $model->getChanges();

            $model->setAmounts();
            $model->resolvePaidStatus();

            if ($model->is_billed) {
                $model->markAsNotBilled();
            }

            EventService::bsOrder($model)
                ->initiator(auth_user())
                ->update()
                ->setHistory([
                    'old_value' => $old,
                    'change_fields' => array_merge($changed, $model->getChanges()),
                ])
                ->exec()
            ;

            return $model->refresh();
        });
    }

    protected function fill(Order $model, OrderDto $dto): Order
    {
        $model->mechanic_id = $dto->mechanicId;
        $model->vehicle_id = $dto->truckId ?? $dto->trailerId;
        $model->vehicle_type = $dto->truckId
            ? Truck::MORPH_NAME
            : Trailer::MORPH_NAME;

        $model->discount = $dto->discount;
        $model->tax_labor = $dto->taxLabor;
        $model->tax_inventory = $dto->taxInventory;
        $model->implementation_date = $dto->implementationDate;
        $model->due_date = $dto->dueDate;
        $model->notes = $dto->notes;

        return $model;
    }

    public function setStatus(Order $model, string $status, bool $save = false): Order
    {
        $model->status = $status;
        $model->status_changed_at = CarbonImmutable::now();

        if($save) $model->save();

        return $model;
    }

    private function generateOrderNumber(): string
    {
        $lastOrder = $this->repo->getLastForOrderNumber();

        $lastNumber = 0;

        if ($lastOrder) {
            $data = explode('-', $lastOrder->order_number);
            $lastNumber = end($data);
        }

        return date('mdY-') . ($lastNumber + 1);
    }

    public function addAttachments(Order $model, array $attachments = []): void
    {
        foreach ($attachments as $attachment) {
            $this->addAttachment($model, $attachment);
        }
    }

    public function addAttachment(Order $model, UploadedFile $file, bool $triggerEvent = false): Order
    {
        try {
            $model->addMediaWithRandomName(Order::ATTACHMENT_COLLECTION_NAME, $file);

            if($triggerEvent){
                $name = str_replace('.'.$file->getClientOriginalExtension(), '', $file->getClientOriginalName());

                /** @var $media Media */
                $media = $model->media()->where('name', $name)->first();
                EventService::bsOrder($model)
                    ->custom(OrderBSHistoryService::ACTION_UPLOAD_FILE)
                    ->initiator(auth_user())
                    ->setMedia($media)
                    ->setHistory()
                    ->exec()
                ;
            }

            return $model;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function deleteFile(Order $model, int $mediaId = 0, bool $triggerEvent = false): void
    {
        if ($media = $model->media->find($mediaId)) {

            if($triggerEvent) $clone = clone $media;

            $model->deleteMedia($mediaId);

            if($triggerEvent){
                EventService::bsOrder($model)
                    ->custom(OrderBSHistoryService::ACTION_DELETE_FILE)
                    ->initiator(auth_user())
                    ->setMedia($clone)
                    ->setHistory()
                    ->exec()
                ;
            }

            return;
        }

        throw new \Exception(__('exceptions.file.not_found'), Response::HTTP_NOT_FOUND);
    }

    public function delete(Order $model): bool
    {
        return make_transaction(function () use ($model){

            $model->status_before_deleting = $model->status;
            $this->setStatus($model, OrderStatus::Deleted->value, true);

            EventService::bsOrder($model)
                ->initiator(auth_user())
                ->setHistory()
                ->delete()
                ->exec()
            ;

            foreach ($model->inventories as $inventory) {
                $this->inventoryService->reduceReservedInOrder(
                    $model,
                    $inventory->inventory,
                    $inventory->quantity,
                    $inventory->price,
                    true
                );
            }

            return $model->delete();
        });
    }

    public function forceDelete(Order $model): bool
    {
        return $model->forceDelete();
    }

    public function reassignMechanic(Order $model, User $mechanic): Order
    {
        return make_transaction(function () use ($model, $mechanic){

            $oldMechanic = $model->mechanic;

            $model->mechanic_id = $mechanic->id;
            $model->save();
            $model->refresh();

            EventService::bsOrder($model)
                ->custom(OrderBSHistoryService::ACTION_REASSIGNED_MECHANIC)
                ->initiator(auth_user())
                ->setHistory([
                    'old_mechanic' => $oldMechanic
                ])
                ->exec()
            ;

            return $model;
        });
    }

    public function changeStatus(Order $model, string $status): Order
    {
        return make_transaction(function () use ($model, $status){

            $oldStatus = $model->status->value;

            $model = $this->setStatus($model, $status);
            $model->save();

            EventService::bsOrder($model)
                ->custom(OrderBSHistoryService::ACTION_STATUS_CHANGED)
                ->initiator(auth_user())
                ->setHistory([
                    'old_status' => $oldStatus
                ])
                ->exec()
            ;

            if (
                OrderStatus::isFinishedFromValue($oldStatus)
                && in_array($status, [OrderStatus::New->value, OrderStatus::In_process->value])
            ) {
                foreach ($model->inventories as $inventory) {
                    $this->inventoryService->reserveOnMovingOrderFromFinished(
                        $model,
                        $inventory->inventory,
                        $inventory->price
                    );
                }

                $model->update([
                    'parts_cost' => null,
                    'profit' => null
                ]);
            }

            if (
                OrderStatus::isFinishedFromValue($status)
                && in_array($oldStatus, [OrderStatus::New->value, OrderStatus::In_process->value])
            ) {
                foreach ($model->inventories as $inventory) {
                    $this->inventoryService->finishedOrderWithInventory(
                        $model,
                        $inventory->inventory,
                        $inventory->price,
                        $inventory->quantity
                    );
                }

                $partsCost = round($model->getPartsCost(), 2);
                $model->update([
                    'parts_cost' => $partsCost,
                    'profit' => $partsCost
                        ? round($model->total_amount - $partsCost, 2)
                        : $model->total_amount
                ]);
            }

            return $model;
        });
    }

    public function restore(Order $model): Order
    {
        return make_transaction(function() use($model) {
            foreach ($model->inventories as $inventory) {
                $this->inventoryService->reserveForOrder(
                    $model,
                    $inventory->inventory,
                    $inventory->quantity,
                    $inventory->price
                );
            }

            $model = $this->setStatus($model, $model->status_before_deleting->value);
            $model->status_before_deleting = null;
            $model->save();
            $model->restore();

            EventService::bsOrder($model)
                ->initiator(auth_user())
                ->setHistory()
                ->custom(OrderBSHistoryService::ACTION_RESTORE)
                ->exec()
            ;

            return $model;
        });
    }

    public function restoreWithEdit(Order $model, OrderDto $dto): Order
    {
        return make_transaction(function() use($model, $dto) {

            $model = $this->update($model, $dto, false);

            $model = $this->restore($model);

            return $model;
        });
    }
}
