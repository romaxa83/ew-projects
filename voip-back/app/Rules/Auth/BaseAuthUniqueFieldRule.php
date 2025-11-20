<?php

namespace App\Rules\Auth;

use App\Models\Admins\Admin;
use App\Models\Employees\Employee;
use App\Models\Users\User;
use Illuminate\Contracts\Validation\Rule;

abstract class BaseAuthUniqueFieldRule implements Rule
{
    protected int|string $ignore;
    protected string $column;
    protected Admin|Employee|string $ignoreModel;

    public function ignoreModel(
        Admin|Employee $model,
        ?string $column = null
    ): self
    {
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

    public function passes($attribute, $value): bool
    {
        if (!$this->checkAdmin($value)) {
            return false;
        }
        if (!$this->checkEmployee($value)) {
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

    protected function checkAdmin(string $value): bool
    {
        $builder = Admin::query()
            ->withTrashed()
            ->where(static::getFieldToCheck(), $this->serializeValue($value));

        if (isset($this->ignoreModel) && $this->ignoreModel === Admin::class) {
            $builder->where($this->column, '<>', $this->ignore);
        }

        return $builder->count() === 0;
    }

    public static function ignoreAdmin(
        Admin|int $id,
        ?string $column = null
    ): self
    {
        $instance = new static();

        if ($id instanceof Admin) {
            return $instance->ignoreModel($id, $column);
        }

        $instance->setIgnoreParams($column, $id, Admin::class);

        return $instance;
    }

    protected function checkEmployee(string $value): bool
    {
        $builder = Employee::query()
            ->withTrashed()
            ->where(static::getFieldToCheck(), $this->serializeValue($value));

        if (isset($this->ignoreModel) && $this->ignoreModel === Employee::class) {
            $builder->where($this->column, '<>', $this->ignore);
        }

        return $builder->count() === 0;
    }

    public static function ignoreEmployee(
        Employee|int $id,
        ?string $column = null
    ): self
    {
        $instance = new static();

        if ($id instanceof Employee) {
            return $instance->ignoreModel($id, $column);
        }

        $instance->setIgnoreParams($column, $id, Employee::class);

        return $instance;
    }

    abstract protected static function getFieldToCheck(): string;

    protected function serializeValue(string $value): string
    {
        return $value;
    }

    public function message(): string
    {
        return static::gerValidationMessage();
    }

    abstract protected static function gerValidationMessage(): string;
}

