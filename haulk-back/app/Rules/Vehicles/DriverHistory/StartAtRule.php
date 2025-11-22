<?php

namespace App\Rules\Vehicles\DriverHistory;

use App\Models\Vehicles\Trailer;
use App\Models\Vehicles\VehicleDriverHistory;
use App\Scopes\CompanyScope;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Validation\Rule;

class StartAtRule implements Rule
{
    protected $startAt;

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  string|null  $value
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        $this->startAt = $value;

        $now = CarbonImmutable::now();

        if(isset(authUser()->getCompany()->timezone)){
            $now = CarbonImmutable::now(authUser()->getCompany()->timezone);
        }

        $startAt = CarbonImmutable::make($value);

        if($now->format('Y-m-d') == $startAt->format('Y-m-d')){
            return false;
        }
        if($now->subDays(VehicleDriverHistory::ADD_HISTORY_DAYS_PAST) > $startAt){
            return false;
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return trans('validation.custom.vehicle.driver_history.start_at', ['date' => $this->startAt]);
    }
}
