<?php

namespace App\Models\Settings;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Settings\EstimateParameters
 *
 * @property int $id
 * @property string|null $division_id
 * @property string $estimate_type
 * @property string $name
 * @property string $value
 * @property string $description
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateParameters interstate()
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateParameters local()
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateParameters newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateParameters newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateParameters query()
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateParameters selected($branch_id)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateParameters whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateParameters whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateParameters whereDivisionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateParameters whereEstimateType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateParameters whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateParameters whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateParameters whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EstimateParameters whereValue($value)
 * @mixin \Eloquent
 */
class EstimateParameters extends Model
{
    protected $table = 'settings_estimate_parameters';

    public function scopeSelected($q, $branch_id)
    {
        return $q
            ->where(function ($q) use ($branch_id) {
                $q->where('division_id', $branch_id)
                    ->orWhereNull('division_id');
            })
            ->orderBy('division_id')
            ->orderBy('estimate_type');
    }

    public function scopeLocal($q)
    {
        return $q->where('estimate_type', 'local');
    }

    public function scopeInterstate($q)
    {
        return $q->where('estimate_type', 'interstate');
    }
}
