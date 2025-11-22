<?php

namespace App\Models\Saas\Pricing;

use Eloquent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int id
 * @property string title
 * @property string slug
 * @property float price_per_driver
 * @property bool is_trial
 * @property string duration
 *
 * @mixin Eloquent
 */
class PricingPlan extends Model
{
    use HasFactory;

    protected $fillable = [
    ];

    protected $casts = [
        'price_per_driver' => 'float',
        'is_trial' => 'boolean',
    ];

    public function getTitle(): string
    {
        return $this->title;
    }

    public function isTrial(): bool
    {
        return $this->is_trial;
    }

    public function isExclusive(): bool
    {
        return $this->slug == config('pricing.plans.haulk-exclusive.slug');
    }

    public function isFree(): bool
    {
        return $this->slug == config('pricing.plans.trial.slug');
    }

    public function getDuration(): string
    {
        return $this->duration;
    }
}
