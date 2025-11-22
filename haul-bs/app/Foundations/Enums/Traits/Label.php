<?php

declare(strict_types=1);

namespace App\Foundations\Enums\Traits;

trait Label
{
    public static function getValuesLabels(): array
    {
        $valuesLabels = [];
        foreach (static::cases() as $case) {
            $valuesLabels[] = [
                'value' => $case->value,
                'label' => $case->label()
            ];
        }
        return $valuesLabels;
    }

    public function label() : string {
        return remove_underscore(ucfirst(strtolower($this->value)));
    }
}
