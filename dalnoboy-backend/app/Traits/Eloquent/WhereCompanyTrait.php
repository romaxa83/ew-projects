<?php

namespace App\Traits\Eloquent;

use App\Models\Companies\Company;
use App\Models\Users\User;
use Illuminate\Database\Eloquent\Builder;

/**
 * @see WhereCompanyTrait::scopeWhereCompany()
 * @method Builder|static whereCompany($company)
 *
 * @see WhereCompanyTrait::scopeWhereNotCompany()
 * @method Builder|static whereNotCompany($company)
 *
 * @see WhereCompanyTrait::scopeWhereSameCompany()
 * @method Builder|static whereSameCompany($user)
 */
trait WhereCompanyTrait
{
    public function scopeWhereCompany(Builder|self $b, Company|int $company): void
    {
        if (isset($this->hasCompanyForeignKey) && $this->hasCompanyForeignKey) {
            $b->where('company_id', toModelKey($company));
            return;
        }

        $b->whereHas('company', fn(Builder $q) => $q->whereKey(toModelKey($company)));
    }

    public function scopeWhereNotCompany(Builder|self $b, Company|int $company): void
    {
        $b->whereDoesntHave('company', fn(Builder $q) => $q->whereKey(toModelKey($company)));
    }

    public function scopeWhereSameCompany(Builder|self $b, User $user): void
    {
        $b->whereCompany($user->company);
    }
}
