<?php

namespace App\ModelFilters\Saas\Company;

use App\Models\Saas\Company\Company;
use EloquentFilter\ModelFilter;
use Illuminate\Database\Eloquent\Builder;

class CompanyFilter extends ModelFilter
{
    public function id($value): void
    {
        $this->where(Company::TABLE_NAME . '.id', $value);
    }

    public function active(string $active): void
    {
        $this->where(Company::TABLE_NAME . '.active', $active);
    }

    public function query($contact): void
    {
        $this->where(
            static function (Builder $builder) use ($contact) {
                $builder
                    ->where(Company::TABLE_NAME . '.name', 'ILIKE', "%{$contact}%")
                    ->orWhere(Company::TABLE_NAME . '.mc_number', 'ILIKE', "%{$contact}%")
                    ->orWhere(Company::TABLE_NAME . '.usdot', 'ILIKE', "%{$contact}%")
                    ->orWhere(Company::TABLE_NAME . '.email', 'ILIKE', "%{$contact}%");
            }
        );
    }

    public function order(string $value): void
    {
        $this->orderBy($value, request('order_type') ?? 'asc');
    }

    public function destroy(array $token): void
    {
        $this->where(Company::TABLE_NAME . '.saas_' . $token['type'] . '_token', $token['value']);
    }

    public function deletion(string $date): void
    {
        $this->where(Company::TABLE_NAME . '.saas_date_delete', $date);
    }

    public function gpsEnabled($enabled): void
    {
        $value = to_bool($enabled);

        if($value){
            $this->has('gpsDeviceSubscription');
        } else {
            $this->doesntHave('gpsDeviceSubscription');
        }
    }
}
