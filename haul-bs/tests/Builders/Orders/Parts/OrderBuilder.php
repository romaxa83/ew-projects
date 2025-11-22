<?php

namespace Tests\Builders\Orders\Parts;

use App\Entities\Order\Parts\EcommerceClientEntity;
use App\Enums\Orders\Parts\DeliveryType;
use App\Enums\Orders\Parts\OrderSource;
use App\Foundations\Entities\Locations\AddressEntity;
use App\Models\Customers\Customer;
use App\Models\Orders\Parts\Order;
use App\Models\Users\User;
use Carbon\CarbonImmutable;
use Tests\Builders\BaseBuilder;

class OrderBuilder extends BaseBuilder
{
    protected array $files = [];

    function modelClass(): string
    {
        return Order::class;
    }

    public function sales_manager(User $model): self
    {
        $this->data['sales_manager_id'] = $model->id;
        return $this;
    }

    public function draft(bool $value): self
    {
        $this->data['draft_at'] = $value
            ? CarbonImmutable::now()
            : null
        ;
        return $this;
    }

    public function draft_at(CarbonImmutable $value): self
    {
        $this->data['draft_at'] = $value;
        return $this;
    }

    public function shipping_method(array $value): self
    {
        $this->data['shipping_method'] = $value;
        return $this;
    }

    public function delivery_type(DeliveryType $value): self
    {
        $this->data['delivery_type'] = $value;
        return $this;
    }

    public function customer(?Customer $model): self
    {
        $this->data['customer_id'] = $model instanceof Customer ? $model->id : null;
        return $this;
    }

    public function ecommerce_client(array $value = []): self
    {
        if(empty($value)){
            $value = [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'john@doe.com',
            ];
        }

        $client = EcommerceClientEntity::make($value);

        $this->data['ecommerce_client'] = $client;
        $this->data['ecommerce_client_email'] = $client->email->getValue();
        $this->data['ecommerce_client_name'] = $client->getFullNameAttribute();
        return $this;
    }

    public function status(string $value, ?CarbonImmutable $date = null): self
    {
        $this->data['status'] = $value;
        if($date){
            $this->data['status_changed_at'] = $date;
        }

        return $this;
    }

    public function source(OrderSource $value): self
    {
        $this->data['source'] = $value;

        return $this;
    }

    public function order_number(string $value): self
    {
        $this->data['order_number'] = $value;
        return $this;
    }

    public function deleted(): self
    {
        $this->data['deleted_at'] = CarbonImmutable::now();
        return $this;
    }

    public function is_paid(bool $value, CarbonImmutable|null $date = null): self
    {
        $this->data['is_paid'] = $value;
        if($date){
            $this->data['paid_at'] = $date;
        }
        return $this;
    }

    public function refunded_at(CarbonImmutable $date = null): self
    {
        if(!$date){
            $date = CarbonImmutable::now();
        }

        $this->data['refunded_at'] = $date;

        return $this;
    }

    public function total_amount(float $value): self
    {
        $this->data['total_amount'] = $value;
        return $this;
    }

    public function paid_amount(float $value): self
    {
        $this->data['paid_amount'] = $value;
        return $this;
    }

    public function debt_amount(float $value): self
    {
        $this->data['debt_amount'] = $value;
        return $this;
    }

    public function payment_method(?string $value): self
    {
        $this->data['payment_method'] = $value;
        return $this;
    }

    public function payment_terms(?string $value): self
    {
        $this->data['payment_terms'] = $value;
        return $this;
    }

    public function delivery_address(array $value): self
    {
        $this->data['delivery_address'] = count($value) != 0 ? $value : null;
        return $this;
    }

    public function delivery_cost(float|int $value): self
    {
        $this->data['delivery_cost'] = $value;
        return $this;
    }

    public function billing_address(array|AddressEntity $value): self
    {
        if($value instanceof AddressEntity){
            $this->data['billing_address'] = $value;
            return $this;
        }
        $this->data['billing_address'] = count($value) != 0 ? $value : null;
        return $this;
    }

    public function is_billed(bool $value, CarbonImmutable|null $date = null): self
    {
        $this->data['is_billed'] = $value;
        if($date){
            $this->data['billed_at'] = $date;
        }
        return $this;
    }

    public function with_tax_exemption(bool $value): self
    {
        $this->data['with_tax_exemption'] = $value;
        return $this;
    }
}
