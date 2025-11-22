<?php

namespace App\Services\Orders\Parts;

use App\Dto\Customers\AddressDto;
use App\Dto\Orders\Parts\OrderDto;
use App\Dto\Orders\Parts\OrderEcomDto;
use App\Enums\Orders\Parts\OrderSource;
use App\Enums\Orders\Parts\OrderStatus;
use App\Enums\Orders\Parts\PaymentMethod;
use App\Foundations\Entities\Locations\AddressEntity;
use App\Foundations\Modules\History\Services\OrderPartsHistoryService;
use App\Models\Customers\Customer;
use App\Models\Orders\Parts\Order;
use App\Models\Users\User;
use App\Repositories\Orders\Parts\OrderRepository;
use App\Services\Customers\CustomerAddressService;
use App\Services\Events\EventService;
use App\Services\Inventories\InventoryService;
use Carbon\CarbonImmutable;
use Illuminate\Http\Response;

class OrderService
{
    public function __construct(
        public OrderRepository $repo,
        public ItemService $itemService,
        public ShippingService $shippingService,
        public OrderStatusService $orderStatusService,
        public InventoryService $inventoryService,
    )
    {}

    public function create(User $user = null): Order
    {
        $model = new Order();
        $model->order_number = $this->generateOrderNumber();
        $model = $this->orderStatusService->changeStatus($model, OrderStatus::New, false);
        $model->source = OrderSource::BS;
        $model->draft_at = CarbonImmutable::now();
        $model->is_paid = false;
        $model->delivery_cost = 0;

        if($user && $user->role->isSalesManager()){
            $model->sales_manager_id = $user->id;
        }

        $model->save();

        $this->shippingService->createDummy($model);

        return $model;
    }

    public function createFromEcomm(
        OrderEcomDto $dto,
        bool $saveHistory = true
    ): Order
    {
        $model = new Order();
        $model->order_number = $this->generateOrderNumber();
        $model = $this->orderStatusService->changeStatus($model, OrderStatus::New, false);
        $model->source = OrderSource::Haulk_Depot;
        $model->customer_id = $dto->customerId;
        $model->delivery_type = $dto->deliveryType;
        $model->delivery_address = $dto->deliveryAddress;
        $model->billing_address = $dto->billingAddress ?? $dto->deliveryAddress;
        $model->payment_method = $dto->paymentMethod;
        $model->with_tax_exemption = $dto->withTaxExemption;
        $model->is_paid = false;
        $model->ecommerce_client = $dto->client;
        $model->ecommerce_client_email = $dto->client->email->getValue();
        $model->ecommerce_client_name = $dto->client->getFullNameAttribute();
        $model->delivery_cost = 0;

        $model->save();

        $this->shippingService->createDummy($model);

        foreach ($dto->items as $itemDto) {
            $this->itemService->create($itemDto, $model, false);
        }

        $model->setAmounts();

        if($saveHistory){
            EventService::partsOrder($model)
                ->initiator(auth_user())
                ->create()
                ->setHistory()
                ->exec()
            ;
        }

        return $model;
    }

    public function update(
        Order $model,
        OrderDto $dto,
        bool $saveHistory = true
    ): Order
    {
        return make_transaction(function () use ($model, $dto, $saveHistory){

            if ($saveHistory) $old = $model->dataForUpdateHistory();

            if($model->isPaid()){
                // обновляем только те поля которые разрешены если заказ оплачен
                $model = $this->updateFieldsIsPaid($model, $dto);
            } else {
                $model = $this->updateFields($model, $dto);
            }

            $model->save();
            $change_1 = $model->getChanges();

            $model->setAmounts();
            $change_2 = $model->getChanges();

            if($saveHistory){
                EventService::partsOrder($model)
                    ->initiator(auth_user())
                    ->update()
                    ->setHistory([
                        'old_value' => $old,
                        'change_fields' => array_merge($change_1, $change_2),
                    ])
                    ->sendToEcomm()
                    ->exec()
                ;
            }

            return $model->refresh();
        });
    }

    // обновление полей, если заказ оплачен
    private function updateFieldsIsPaid(Order $model, OrderDto $dto): Order
    {
        $model->source = $dto->source;

        $model->delivery_address->first_name = $dto->deliveryAddress->first_name;
        $model->delivery_address->last_name = $dto->deliveryAddress->last_name;
        $model->delivery_address->company = $dto->deliveryAddress->company;
        $model->delivery_address->address = $dto->deliveryAddress->address;

        if($model->billing_address) {
            $model->billing_address->first_name = $dto->billingAddress->first_name ?? $model->billing_address->first_name;
            $model->billing_address->last_name = $dto->billingAddress->last_name ?? $model->billing_address->last_name;
            $model->billing_address->company = $dto->billingAddress->company ?? $model->billing_address->company;
            $model->billing_address->address = $dto->billingAddress->address ?? $model->billing_address->address;
        }

        return $model;
    }

    private function updateFields(Order $model, OrderDto $dto): Order
    {
        $model->customer_id = $dto->customerId;
        $model->source = $dto->source;
        $model->delivery_type = $dto->deliveryType;
        $model->delivery_address = $dto->deliveryAddress;
        $model->billing_address = $dto->billingAddress;
        $model->payment_method = $dto->paymentMethod;
        $model->payment_terms = $dto->paymentTerms;
        $model->with_tax_exemption = $dto->withTaxExemption;
        $model->delivery_cost = $dto->deliveryCost;

        return $model;
    }

    public function checkout(Order $model, User $user = null): Order
    {
        if(!$model->isDraft()){
            throw new \Exception(
                __('exceptions.orders.parts.must_be_draft'),
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }
        if(!$model->delivery_type){
            throw new \Exception(
                __('exceptions.orders.parts.must_have_delivery_type'),
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }
        if(
            $model->delivery_type->isPickup()
            && !$model->billing_address
        ){
            throw new \Exception(
                __('exceptions.orders.parts.must_have_billing_address'),
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }
        if(
            $model->delivery_type->isDelivery()
            && !$model->delivery_address
        ){
            throw new \Exception(
                __('exceptions.orders.parts.must_have_delivery_address'),
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }
        if($model->items->count() < 1){
            throw new \Exception(
                __('exceptions.orders.parts.must_have_items'),
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }
        if(!$model->payment_method){
            throw new \Exception(
                __('exceptions.orders.parts.must_have_payment_methods'),
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }
        if($model->hasOverloadInventory() && $model->delivery_type->isDelivery()){
            throw new \Exception(
                __("validation.custom.order.parts.has_overload"),
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }
        return make_transaction(function () use ($model, $user){

            $model->draft_at = null;

            if($user && $user->role->isSalesManager()){
                $model->sales_manager_id = $user->id;
                $model = $this->orderStatusService->changeStatus($model, OrderStatus::In_process, false);
            }

            if(!$model->billing_address){
                $model->billing_address = $model->delivery_address;
            }

            $model->save();

            if($model->delivery_address?->save){
                $this->saveDeliveryAddressToCustomer($model->delivery_address, $model->customer_id);
            }

            $model->setAmounts();

            foreach ($model->items as $item){
                $this->inventoryService->reserveForOrder(
                    order: $model,
                    inventory: $item->inventory,
                    quantity: $item->qty,
                    price: $item->getPriceForTransaction(),
                );
            }

            EventService::partsOrder($model)
                ->create()
                ->initiator(auth_user())
                ->setHistory()
                ->exec();

            if(in_array($model->payment_method, PaymentMethod::forOnline())){
                /** @var $paymnetService OrderPaymentService */
                $paymentService = resolve(OrderPaymentService::class);
                $paymentService->sendLink($model);
            }

            return $model->refresh();
        });
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

    public function delete(Order $model): bool
    {
        return make_transaction(function () use ($model){

            foreach ($model->items as $item){
                $this->itemService->delete($item, false);
            }

            if($model->isDraft()){
                $res = $model->forceDelete();
            } else {
                $model->status_before_deleting = $model->status;
                $model->save();

                EventService::partsOrder(clone $model)
                    ->delete()
                    ->initiator(auth_user())
                    ->sendToEcomm()
                    ->exec();

                $res = $model->delete();
            }

            return $res;
        });
    }

    protected function saveDeliveryAddressToCustomer(
        AddressEntity $address,
        int|string $customerId
    ): void
    {
        $customer = Customer::find($customerId);

        if(!$customer->canAddNew()){
            throw new \Exception(
                __('exceptions.customer.address.more_limit'),
                Response::HTTP_BAD_REQUEST
            );
        }

        $service = resolve(CustomerAddressService::class);
        $service->create(AddressDto::byArgs([
            'first_name' => $address->first_name,
            'last_name' => $address->last_name,
            'company_name' => $address->company,
            'address' => $address->address,
            'state' => $address->state,
            'city' => $address->city,
            'zip' => $address->zip,
            'phone' => $address->phone->getValue(),
            'is_default' => false,
        ]), $customer);
    }

    public function assignSalesManager(Order $model, User $sales): Order
    {
        return make_transaction(function () use ($model, $sales){

            $history = [
                'old_sales_manager' => $model->salesManager ? clone $model->salesManager : null,
                'old_status' => $model->status,
            ];

            $model->sales_manager_id = $sales->id;

            if($model->status->isNew()){
                $model = $this->orderStatusService->changeStatus($model, OrderStatus::In_process, false);
            }

            $model->save();
            $model->refresh();

            EventService::partsOrder($model)
                ->custom(
                    $history['old_sales_manager']
                        ? OrderPartsHistoryService::ACTION_REASSIGN_SALES_MANAGER
                        : OrderPartsHistoryService::ACTION_ASSIGN_SALES_MANAGER
                )
                ->initiator(auth_user())
                ->setHistory($history)
                ->sendToEcomm(OrderPartsHistoryService::ACTION_STATUS_CHANGED)
                ->exec()
            ;

            return $model;
        });
    }

    public function refunded(Order $model): Order
    {
        if(!$model->isPaid()){
            throw new \Exception(
                __('exceptions.orders.must_be_paid'),
                Response::HTTP_BAD_REQUEST
            );
        }
        if(!$model->canRefunded()){
            throw new \Exception(
                __('exceptions.orders.parts.cant_change_refunded'),
                Response::HTTP_BAD_REQUEST)
            ;
        }

        $model->refunded_at = CarbonImmutable::now();
        $model->save();

        EventService::partsOrder($model)
            ->custom(OrderPartsHistoryService::ACTION_REFUNDED)
            ->initiator(auth_user())
            ->setHistory()
            ->sendToEcomm()
            ->exec()
        ;

        return $model;
    }
}
