<?php


namespace App\Rules;


use App\Models\Users\User;
use App\Traits\Eloquent\WhereCompanyTrait;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

abstract class AbstractBelongsToTheSameCompanyRule implements Rule
{
    protected string|Model $model;

    public function __construct(
        protected ?User $user = null
    ) {
    }

    public function passes($attribute, $value): bool
    {
        return $this->model::query()
            ->whereKey($value)
            ->when(
                $this->user,
                function (Builder $query) {
                    /** @var Builder|WhereCompanyTrait $query */
                    $query->whereSameCompany($this->user);
                }
            )->exists();
    }

    public function message(): string
    {
        return __('validation.exists');
    }
}
