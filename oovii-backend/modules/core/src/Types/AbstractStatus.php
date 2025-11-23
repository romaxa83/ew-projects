<?php

namespace WezomCms\Core\Types;

abstract class AbstractStatus
{
    protected int $value;

    // массив типов(статусов), где ключ значение, а значение перевод
    abstract public static function list(): array;

    // проверка наличие типа(статуса)
    public static function check($status): bool
    {
        return array_key_exists($status, static::list());
    }

    // проверка наличие типа(статуса), которое выкинет исключение
    public static function assert($status): void
    {
        if(!array_key_exists($status, static::list())){
            static::exceptionMessage($status);
        }
    }

    protected static function exceptionMessage($status = null): void
    {
        throw new \InvalidArgumentException("Invalid status [{$status}]");
    }

    public static function create(int $value): self
    {
        static::assert($value);

        $self = new static();
        $self->value = $value;

        return $self;
    }

    public function getValue(): int
    {
        return $this->value;
    }
}

