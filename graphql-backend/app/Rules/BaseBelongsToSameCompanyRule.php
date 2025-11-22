<?php

namespace App\Rules;

use App\Models\BaseModel;
use App\Models\Users\User;
use App\Traits\Eloquent\WhereCompanyTrait;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Database\Eloquent\Builder;

abstract class BaseBelongsToSameCompanyRule implements Rule
{
    protected string|BaseModel $model;

    public function __construct(
        protected ?User $user = null
    ) {
    }

    public function passes($attribute, $value): bool
    {
        if (empty($value)) {
            return false;
        }

        $count = is_countable($value)
            ? count($value)
            : 1;

        return $this->model::query()
                ->whereKey($value)
                ->where(fn(Builder $b) => $this->criteria($b))
                ->when(
                    $this->user,
                    fn(Builder /** @var Builder|WhereCompanyTrait $q */ $q) => $q->whereSameCompany($this->user)
                )
                ->count() === $count;
    }

    public function message(): string
    {
        return __('validation.exists');
    }

    protected function criteria(Builder $b): void
    {
    }
}
