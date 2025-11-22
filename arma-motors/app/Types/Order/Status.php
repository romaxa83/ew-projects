<?php

namespace App\Types\Order;

use App\Types\AbstractType;

final class Status extends AbstractType
{
    const DRAFT      = 0;   // создана пользователем, текущая
    const CREATED    = 1;   // принята в работу (AA), проставлен исполнитель (SYS), текущая
    const IN_PROCESS = 2;   // в работе (АА, SYS), текущая
    const DONE       = 3;   // Выполнена(АА), + PaymentNot, PaymentPart - текущая
    const REJECT     = 4;   // Отклонена (SYS), архив
    const CLOSE      = 5;   // Выполнена и оплачена, история

    public static function list(): array
    {
        return [
            self::DRAFT => __('translation.order.status.draft'),
            self::CREATED => __('translation.order.status.created'),
            self::IN_PROCESS => __('translation.order.status.in_process'),
            self::DONE => __('translation.order.status.done'),
            self::CLOSE => __('translation.order.status.close'),
            self::REJECT => __('translation.order.status.reject'),
        ];
    }

    // статуса для заявок - текущие (mobile)
    public static function statusForCurrent(): array
    {
        return [
            self::DRAFT,
            self::CREATED,
            self::IN_PROCESS,
            self::DONE
        ];
    }

    // статуса для заявок - история (mobile)
    public static function statusForHistory(): array
    {
        return [
            self::CLOSE
        ];
    }

    public static function create($value): self
    {
        static::assert($value);

        $self = new self();
        $self->value = $value;

        return $self;
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public function isDraft(): bool
    {
        return $this->value === self::DRAFT;
    }

    public function isCreated(): bool
    {
        return $this->value === self::CREATED;
    }

    public function isProcess(): bool
    {
        return $this->value === self::IN_PROCESS;
    }

    public function isDone(): bool
    {
        return $this->value === self::DONE;
    }

    public function isClose(): bool
    {
        return $this->value === self::CLOSE;
    }

    public function isReject(): bool
    {
        return $this->value === self::REJECT;
    }

    protected static function exceptionMessage(array $replace = []): string
    {
        return __('error.not valid order status', $replace);
    }

    public static function isCloseStatus($status): bool
    {
        return $status === self::CLOSE;
    }

    public static function isDraftStatus($status): bool
    {
        return $status === self::DRAFT;
    }

    public static function isRejectStatus($status): bool
    {
        return $status === self::REJECT;
    }

    public static function isCreateStatus($status): bool
    {
        return $status === self::CREATED;
    }

    public static function isProcessStatus($status): bool
    {
        return $status === self::IN_PROCESS;
    }
}

