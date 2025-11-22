<?php

namespace App\Traits\Eloquent;

use App\Models\Companies\Company;
use App\Models\Users\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use RuntimeException;

/**
 * @see WhereCompanyTrait::scopeWhereCompany()
 * @method Builder|static whereCompany($company)
 *
 * @see WhereCompanyTrait::scopeWhereSameCompany()
 * @method Builder|static whereSameCompany($user)
 */
trait WhereCompanyTrait
{
    public function scopeWhereCompany(Builder|self $b, Company|int $company): void
    {
        if (!method_exists($this, 'company')) {
            throw new RuntimeException('Relation method "company" not exists!');
        }

        if (!is_object($result = $this->company())) {
            throw new RuntimeException('Relation method "company" returned null!');
        }

        if ($result instanceof BelongsTo) {
            $b->where('company_id', to_model_key($company));
            return;
        }

        if ($result instanceof HasManyThrough) {
            $b->whereHas('company', fn(Builder $q) => $q->whereKey(to_model_key($company)));
            return;
        }

        throw new RuntimeException('Bad relation method "company"!');
    }

    public function scopeWhereSameCompany(Builder|self $b, User $user): void
    {
        $b->whereCompany($user->company);
    }
}
