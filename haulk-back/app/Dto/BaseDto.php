<?php

namespace App\Dto;

use App\Http\Controllers\Api\Helpers\DateTimeHelper;
use ArrayAccess;
use Exception;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use ReflectionClass;
use ReflectionProperty;

abstract class BaseDto implements ArrayAccess, Arrayable
{
    use HasArrayAccessDto;

    abstract public static function init(array $args): self;

    public function __get(string $name)
    {
        if (property_exists($this, $name)) {
            return $this->{$name};
        }

        return null;
    }

    /**
     * @param $name
     * @param $arguments
     * @return bool
     * @throws Exception
     */
    public function __call($name, $arguments)
    {
        $result = $this->checkIssetMethod($name);

        if ($result === null) {
            throw new Exception("Method " . $name . " not found", $this, $name, $arguments);
        }

        return $result;
    }

    protected function checkIssetMethod(string $name): ?bool
    {
        if (!preg_match("/^isset/", $name)) {
            return null;
        }

        $propertyName = lcfirst(
            preg_replace("/^isset/", "", $name)
        );

        return $this->issetProperty($propertyName);
    }

    protected function issetProperty(string $propertyName): bool
    {
        if (!property_exists($this, $propertyName)) {
            return false;
        }

        if (!isset($this->{$propertyName}) || $this->{$propertyName} === null) {
            return false;
        }

        return true;
    }

    public function toArray(bool $withNull = true): ?array
    {
        $reflection = new ReflectionClass($this);
        $properties = $reflection->getProperties(ReflectionProperty::IS_PROTECTED);

        foreach ($properties as $property) {
            if (!$this->issetProperty($property->name)) {
                if (!$withNull) {
                    continue;
                }
                $result[Str::snake($property->name)] = null;
                continue;
            }
            $value = $this->renderToArrayValue($this->{$property->name}, $withNull);

            if ($value === null && !$withNull) {
                continue;
            }

            $result[Str::snake($property->name)] = $value;
        }

        return !empty($result) ? $result : null;
    }

    private function renderToArrayValue($value, bool $withNull)
    {
        if ($value instanceof BaseDto) {
            return $value->toArray($withNull);
        } elseif ($value instanceof Collection) {
            return $value->toArray();
        } elseif ($value instanceof Carbon) {
            return method_exists($this, 'renderCarbonValue') ? $this->renderCarbonValue($value) :
                DateTimeHelper::toDateTime($value);
        } elseif (is_array($value)) {
            foreach ($value as $key => $item) {
                $item = $this->renderToArrayValue($item, $withNull);
                if ($item === null && !$withNull) {
                    continue;
                }
                $result[Str::snake($key)] = $item;
            }
            return !empty($result) ? $result : null;
        } else {
            return $value;
        }
    }
}
