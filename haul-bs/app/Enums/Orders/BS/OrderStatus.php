<?php

namespace App\Enums\Orders\BS;

use App\Foundations\Enums\Traits\InvokableCases;

enum OrderStatus: string {

    use InvokableCases;

    case New = 'new';
    case In_process = 'in_process';
    case Finished = 'finished';
    case Deleted = 'deleted';

    public function label(): string
    {
        return match ($this->value){
            static::New->value => 'New',
            static::In_process->value => 'In process',
            static::Finished->value => 'Finished',
            static::Deleted->value => 'Deleted',
            default => throw new \Exception('Unexpected match value'),
        };
    }

    public static function isDeletedFromValue(string $value): bool
    {
        return $value === OrderStatus::Deleted->value;
    }

    public static function isFinishedFromValue(string $value): bool
    {
        return $value === OrderStatus::Finished->value;
    }

    public function isNew(): bool
    {
        return $this === self::New;
    }

    public function isFinished(): bool
    {
        return $this === self::Finished;
    }

    public function isDeleted(): bool
    {
        return $this === self::Deleted;
    }
}
