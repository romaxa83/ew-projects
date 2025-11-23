<?php

namespace WezomCms\Orders\Traits;

use Illuminate\Support\Collection;
use InvalidArgumentException;
use WezomCms\Orders\Contracts\CartConditionInterface;
use WezomCms\Orders\Contracts\CartItemConditionInterface;

trait CartConditionsTrait
{
    /**
     * Conditions container.
     *
     * @var array
     */
    private $conditions = [];

    /**
     * Registered global conditions.
     *
     * @var array
     */
    protected static $globalConditions = [];

    /**
     * @param  CartConditionInterface|CartItemConditionInterface  $condition
     * @return bool
     * @throws InvalidArgumentException
     */
    public function applyCondition($condition): bool
    {
        if (!$condition instanceof CartConditionInterface && !$condition instanceof CartItemConditionInterface) {
            throw new InvalidArgumentException(sprintf('Unsupported condition [%s]', get_class($condition)));
        }

        if ($condition->valid() === false || $this->hasCondition($condition)) {
            return false;
        }

        $this->conditions[] = $condition;

        return true;
    }

    /**
     * @param  CartConditionInterface|CartItemConditionInterface  $condition
     *
     * @return void
     */
    public static function addGlobalCondition($condition): void
    {
        if (!$condition instanceof CartConditionInterface && !$condition instanceof CartItemConditionInterface) {
            throw new InvalidArgumentException(sprintf('Unsupported condition [%s]', get_class($condition)));
        }

        if ($condition->valid() === false || static::hasGlobalCondition($condition)) {
            return;
        }

        static::$globalConditions[] = $condition;
    }


    /**
     * @param  string  $conditionName
     * @return bool
     */
    public function isAppliedCondition(string $conditionName): bool
    {
        return $this->getCartConditions()
            ->filter(function (CartConditionInterface $condition) use ($conditionName) {
                return $condition instanceof $conditionName;
            })
            ->isNotEmpty();
    }

    /**
     * @return bool
     */
    public function hasConditions(): bool
    {
        return !empty($this->getAllRegisteredConditions());
    }

    /**
     * @param  CartConditionInterface|CartItemConditionInterface|string  $condition
     * @return bool
     */
    public function hasCondition($condition): bool
    {
        return collect($this->conditions)
            ->when(!is_object($condition), function (Collection $collection) {
                return $collection->map(function ($item) {
                    return get_class($item);
                });
            })
            ->contains($condition);
    }

    /**
     * @param  CartConditionInterface|CartItemConditionInterface|string  $condition
     * @return bool
     */
    public static function hasGlobalCondition($condition): bool
    {
        return collect(static::$globalConditions)
            ->when(!is_object($condition), function (Collection $collection) {
                return $collection->map(function ($item) {
                    return get_class($item);
                });
            })
            ->contains($condition);
    }

    /**
     * @return Collection
     */
    public function getConditions(): Collection
    {
        return $this->sort($this->conditions);
    }

    /**
     * @param  string  $conditionName
     *
     * @return null|CartItemConditionInterface|CartConditionInterface
     */
    public function getCondition(string $conditionName)
    {
        return collect($this->getAllRegisteredConditions())
            ->first(function ($condition) use ($conditionName) {
                return get_class($condition) === $conditionName;
            });
    }

    /**
     * @return Collection|CartConditionInterface[]
     */
    public function getCartConditions(): Collection
    {
        return $this->sort(array_filter($this->getAllRegisteredConditions(), function ($condition) {
            return $condition instanceof CartConditionInterface;
        }));
    }

    /**
     * @return Collection|CartItemConditionInterface[]
     */
    public function getItemConditions(): Collection
    {
        return $this->sort(array_filter($this->getAllRegisteredConditions(), function ($condition) {
            return $condition instanceof CartItemConditionInterface;
        }));
    }

    /**
     * @param  CartConditionInterface|CartItemConditionInterface  $condition
     * @return bool
     */
    public function removeCondition($condition): bool
    {
        if (($index = array_search($condition, $this->conditions)) !== false) {
            unset($this->conditions[$index]);

            return true;
        }

        return false;
    }

    /**
     * @param  CartConditionInterface|CartItemConditionInterface  $condition
     * @return bool
     */
    public static function removeGlobalCondition($condition): bool
    {
        if (($index = array_search($condition, static::$globalConditions)) !== false) {
            unset(static::$globalConditions[$index]);

            return true;
        }

        return false;
    }

    /**
     * Delete all conditions.
     *
     * @return bool
     */
    public function clearConditions(): bool
    {
        $this->conditions = [];
        static::$globalConditions = [];

        return true;
    }

    /**
     * @return array
     */
    protected function getAllRegisteredConditions(): array
    {
        return array_merge($this->conditions, static::$globalConditions);
    }

    /**
     * @param  array  $conditions
     * @return Collection
     */
    protected function sort(array $conditions): Collection
    {
        $conditions = collect($conditions);

        return $conditions->sortBy(function ($condition) {
            /** @var CartConditionInterface|CartItemConditionInterface $condition */
            return $condition->getPriority();
        });
    }
}
