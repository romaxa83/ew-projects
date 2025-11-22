<?php

namespace App\Foundations\Modules\History\Strategies\Details;

abstract class BaseDetailsStrategy implements DetailsStrategy
{
    const TYPE_ADDED = 'added';
    const TYPE_UPDATED = 'updated';
    const TYPE_REMOVED = 'removed';

    abstract public function getDetails(): array;

    protected function jsonFields(): array
    {
        return [];
    }

    // проверяет наличие полей в additional, если их нет кидает исключение
    protected function checkFieldsForAdditional(array $fields): void
    {
        foreach ($fields as $field) {
            if(!(isset($this->additional[$field]))){
                throw new \Exception('[DetailsStrategy] you need transfer a '.$field);
            }
        }
    }
}
