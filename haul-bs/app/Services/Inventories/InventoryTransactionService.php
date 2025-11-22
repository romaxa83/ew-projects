<?php

namespace App\Services\Inventories;

use App\Dto\Inventories\PurchaseDto;
use App\Dto\Inventories\SoldDto;
use App\Foundations\Modules\History\Services\InventoryHistoryService;
use App\Models\Inventories\Inventory;
use App\Models\Inventories\Transaction;
use App\Services\Events\EventService;

final readonly class InventoryTransactionService
{
    public function __construct()
    {}

    public function create(Inventory $inventory, PurchaseDto|SoldDto $dto): Transaction
    {
        $old = $inventory->dataForUpdateHistory();
        $event = EventService::inventory($inventory)
            ->initiator(auth_user())
        ;

        $transaction = $this->addTransaction($inventory, $dto);

        if($transaction->operation_type->isPurchase()){
            $action = InventoryHistoryService::ACTION_QUANTITY_INCREASED;
        } else {
            $action = InventoryHistoryService::ACTION_QUANTITY_DECREASED;
            if($transaction->describe->isSold()){
                $action = InventoryHistoryService::ACTION_QUANTITY_DECREASED_SOLD;
            }
        }

        $event->custom($action)
            ->setHistory($old + ['transaction_id' => $transaction->id])
            ->exec();

        return $transaction;
    }

    public function addTransaction(
        Inventory $inventory,
        PurchaseDto|SoldDto $dto,
        bool $fromReserve = false
    ): Transaction
    {
        $model = new Transaction();
        $model->inventory_id = $inventory->id;
        $model->quantity = $dto->qty;
        $model->price = $dto->price;
        $model->operation_type = $dto->operationType;
        $model->transaction_date = $dto->transactionDate;
        $model->invoice_number = $dto->invoiceNumber;

        if($dto instanceof SoldDto){
            $model->describe = $dto->describe;
            $model->discount = $dto->discount;
            $model->tax = $dto->tax;
            $model->payment_method = $dto->paymentMethod;
            $model->payment_date = $dto->paymentDate;
            $model->first_name = $dto->firstName;
            $model->last_name = $dto->lastName;
            $model->company_name = $dto->companyName;
            $model->phone = $dto->phone;
            $model->email = $dto->email;
        }

        $model->save();

        if (!$fromReserve && $model->operation_type->isPurchase()) {
            $inventory->increaseQuantity($model->quantity);
        }

        if (!$fromReserve && $model->operation_type->isSold()) {
            $inventory->decreaseQuantity($model->quantity);
        }

        return $model;
    }
}
