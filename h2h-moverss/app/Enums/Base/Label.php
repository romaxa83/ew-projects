<?php

declare(strict_types=1);

namespace App\Enums\Base;

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

    public static function getLabelAsNameByValue(null|int|string $value): ?string
    {
        if(is_null($value)){
            return null;
        }
        if($value == '0'){
            return null;
        }

        $target = null;
        foreach (static::cases() as $case) {
            if($case->value == $value){
                $target = $case;
            }
        }

        if(is_null($target)){
            throw new \Exception('Enum not found by value: ' . $value );
        }

        return $target->labelAsName();
    }

    public function label() : string
    {
        return str_replace('_', ' ', ucfirst(strtolower($this->value)));
    }

    public function labelAsName() : string
    {
        return str_replace('_', ' ', ucfirst(strtolower($this->name)));
    }
}

