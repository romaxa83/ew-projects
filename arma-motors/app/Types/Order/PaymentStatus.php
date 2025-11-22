<?php

namespace App\Types\Order;

use App\Types\AbstractType;

final class PaymentStatus extends AbstractType
{
    const NONE = 0;   // нет статуса
    const NOT  = 1;   // не оплачено
    const PART = 2;   // частично
    const FULL = 3;   // полностью

    public static function list(): array
    {
        return [
            self::NONE => __('translation.order.payment.none'),
            self::NOT => __('translation.order.payment.not'),
            self::PART => __('translation.order.payment.part'),
            self::FULL => __('translation.order.payment.full')
        ];
    }

    public static function create($value): self
    {
        static::assert($value);

        $self = new self();
        $self->value = $value;

        return $self;
    }

    public function isNone(): bool
    {
        return $this->value === self::NONE;
    }

    public function isNot(): bool
    {
        return $this->value === self::NOT;
    }

    public function isPart(): bool
    {
        return $this->value === self::PART;
    }

    public function isFull(): bool
    {
        return $this->value === self::FULL;
    }

    protected static function exceptionMessage(array $replace = []): string
    {
        return __('error.not valid order payment status', $replace);
    }
}
