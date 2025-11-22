<?php

namespace App\Models\Saas\Pricing;

use App\Models\Saas\Company\Company;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property Carbon|null $billing_start
 * @property Carbon|null $billing_end
 * @mixin \Eloquent
 *
 * @see static::company()
 * @property Company|BelongsTo company
 *
 * @see static::pricingPlan()
 * @property PricingPlan|null pricingPlan
 */

class CompanySubscription extends Model
{
    use HasFactory;

    public const TABLE_NAME = 'company_subscriptions';

    protected $casts = [
        'canceled' => 'boolean',
        'is_trial' => 'boolean',
    ];

    protected $dates = [
        'billing_start',
        'billing_end',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * @return BelongsTo
     */
    public function pricingPlan(): BelongsTo
    {
        return $this->belongsTo(PricingPlan::class);
    }

    public function isActive(): bool
    {
        return !(
            $this->isCanceled()
            || $this->billingEndReached()
        );
    }

    public function isInTrialPeriod(): bool
    {
        return $this->isTrial()
            && !$this->billingEndReached();
    }

    public function isTrialExpired(): bool
    {
        return $this->isTrial()
            && $this->billingEndReached();
    }

    public function isTrial(): bool
    {
        return $this->is_trial;
    }

    public function isCanceled(): bool
    {
        return $this->canceled;
    }

    public function billingEndReached(): bool
    {
        return $this->billing_end->timestamp < now()->timestamp;
    }
}
