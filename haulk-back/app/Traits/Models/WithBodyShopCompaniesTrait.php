<?php

namespace App\Traits\Models;

use App\Models\Saas\Company\Company;
use Illuminate\Database\Eloquent\Builder;

/**
 *
 * @method static Builder withBodyShopCompanies()
 *
 * @package App\Traits\Models
 */
trait WithBodyShopCompaniesTrait
{
    public function scopeWithBodyShopCompanies(Builder $builder)
    {
        $companies = Company::where('use_in_body_shop', true)->get()->pluck('id');

        $builder->where(function(Builder $query) use ($companies) {
            $query->whereNull(['carrier_id', 'broker_id'])
                ->orWhere(
                    function (Builder $q) use ($companies) {
                        $q->whereIn('carrier_id', $companies)
                            ->orWhereIn('broker_id', $companies);
                    }
                );
        });
    }
}
