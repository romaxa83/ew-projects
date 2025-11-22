<?php

namespace App\Collections\Statistics\Solutions;

use App\Entities\Statistics\Solutions\IndoorEntity;
use ArrayIterator;
use Illuminate\Support\Collection;
use JsonException;

/**
 * @method IndoorEntity|null first(callable $callback = null, $default = null)
 * @method IndoorEntity|null last(callable $callback = null, $default = null)
 * @method IndoorEntity|null get($key, $default = null)
 * @method IndoorEntity|null pop()
 * @method IndoorEntity|null shift()
 * @method ArrayIterator|IndoorEntity[] getIterator()
 *
 * @property IndoorEntity[] items
 */
class IndoorsCollection extends Collection
{
    /**
     * @throws JsonException
     */
    public static function getJson(mixed $value): string
    {
        return self::resolve($value)->toJson();
    }

    /**
     * @throws JsonException
     */
    public static function resolve(mixed $value): self
    {
        if (is_string($value)) {
            return (new self())->createFromString($value);
        }

        if ($value instanceof Collection) {
            $value = $value->toArray();
        }

        if (is_array($value)) {
            return (new self())->createFromArray($value);
        }

        if ($value instanceof self) {
            return $value;
        }

        return self::make();
    }

    /**
     * @throws JsonException
     */
    protected function createFromString(string $value): self
    {
        return $this->createFromArray(jsonToArray($value));
    }

    protected function createFromArray(array $value): self
    {
        $self = self::make();

        $index = 1;

        foreach ($value as $item) {
            $self->put($index++, IndoorEntity::make($item));
        }

        return $self;
    }
}