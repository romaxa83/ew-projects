<?php

namespace App\Rules;

use App\Models\Dealers\Dealer;
use App\Models\Technicians\Technician;
use App\Models\Users\User;
use Illuminate\Contracts\Validation\Rule;

abstract class BaseMemberUniqueFieldRule implements Rule
{
    protected int|string $ignore;
    protected string $column;
    protected User|Technician|Dealer|string $ignoreModel;

    public static function ignoreUser(
        User|int $id,
        ?string $column = null
    ): self {
        $instance = new static();

        if ($id instanceof User) {
            return $instance->ignoreModel($id, $column);
        }

        $instance->setIgnoreParams($column, $id, User::class);

        return $instance;
    }

    public function ignoreModel(
        User|Technician|Dealer $model,
        ?string $column = null
    ): self {
        $this->column = $column ?? $model->getKeyName();
        $this->ignore = $model->{$this->column};
        $this->ignoreModel = $model;

        return $this;
    }

    protected function setIgnoreParams(
        ?string $column,
        int $id,
        string $ignoreModel
    ): void {
        $this->column = $column ?? 'id';
        $this->ignore = $id;
        $this->ignoreModel = $ignoreModel;
    }

    public static function ignoreTechnician(
        Technician|int $id,
        ?string $column = null
    ): self {
        $instance = new static();

        if ($id instanceof Technician) {
            return $instance->ignoreModel($id, $column);
        }

        $instance->setIgnoreParams($column, $id, Technician::class);

        return $instance;
    }

    public function passes($attribute, $value): bool
    {
        if (!$this->checkUser($value)) {
            return false;
        }

        if (!$this->checkTechnician($value)) {
            return false;
        }

        if (!$this->checkDealer($value)) {
            return false;
        }

        return true;
    }

    protected function checkUser(string $value): bool
    {
        $builder = User::query()
            ->withTrashed()
            ->where(static::getFieldToCheck(), $this->serializeValue($value));

        if (isset($this->ignoreModel) && $this->ignoreModel === User::class) {
            $builder->where($this->column, '<>', $this->ignore);
        }

        return $builder->count() === 0;
    }

    abstract protected static function getFieldToCheck(): string;

    protected function serializeValue(string $value): string
    {
        return $value;
    }

    protected function checkTechnician(string $value): bool
    {
        $builder = Technician::query()
            ->withTrashed()
            ->where(static::getFieldToCheck(), $this->serializeValue($value));

        if (isset($this->ignoreModel)
            && $this->ignoreModel === Technician::class
        ) {
            $builder->where($this->column, '<>', $this->ignore);
        }

        return $builder->count() === 0;
    }

    protected function checkDealer(string $value): bool
    {
        $builder = Dealer::query()
            ->where(static::getFieldToCheck(), $this->serializeValue($value));

        if (isset($this->ignoreModel) && $this->ignoreModel === Dealer::class) {
            $builder->where($this->column, '<>', $this->ignore);
        }

        return $builder->count() === 0;
    }

    public function message(): string
    {
        return static::gerValidationMessage();
    }

    abstract protected static function gerValidationMessage(): string;
}
