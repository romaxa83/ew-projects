<?php

namespace App\Dto\Orders;


use Carbon\Carbon;

class OrderPaymentDto
{

    private ?float $orderPrice = null;
    private ?float $orderPriceWithDiscount = null;
    private ?float $shippingCost = null;
    private ?float $tax = null;
    private ?float $discount = null;
    private ?int $paidAt = null;

    /**
     * @param array|null $args
     * @return OrderPaymentDto
     */
    public static function byArgs(?array $args): OrderPaymentDto
    {
        $dto = new self();

        if (empty($args)) {
            return $dto;
        }

        if (($value = data_get($args, 'order_price')) !== null) {
            $dto->orderPrice = (float)$value;
        }

        if (($value = data_get($args, 'order_price_with_discount')) !== null) {
            $dto->orderPriceWithDiscount = (float)$value;
        }

        if (($value = data_get($args, 'shipping_cost')) !== null) {
            $dto->shippingCost = (float)$value;
        }

        if (($value = data_get($args, 'tax')) !== null) {
            $dto->tax = (float)$value;
        }

        if (($value = data_get($args, 'discount')) !== null) {
            $dto->discount = (float)$value;
        }

        if (($value = data_get($args, 'paid_at')) !== null) {
            $dto->paidAt = Carbon::parse($value)
                ->getTimestamp();
        }

        return $dto;
    }

    public function getOrderPrice(): ?float
    {
        return $this->orderPrice;
    }

    public function getOrderPriceWithDiscount(): ?float
    {
        return $this->orderPriceWithDiscount;
    }

    public function getShippingCost(): ?float
    {
        return $this->shippingCost;
    }

    public function getTax(): ?float
    {
        return $this->tax;
    }

    public function getDiscount(): ?float
    {
        return $this->discount;
    }

    public function getPaidAt(): ?int
    {
        return $this->paidAt === null || $this->getOrderPrice() === null ? null : $this->paidAt;
    }

    public function isExistsPayment(): bool
    {
        return $this->orderPrice !== null;
    }
}

