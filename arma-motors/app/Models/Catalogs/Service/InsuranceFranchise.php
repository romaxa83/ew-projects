<?php

namespace App\Models\Catalogs\Service;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property int $id
 * @property bool $active
 * @property int $sort
 * @property string $name
 *
 */
class InsuranceFranchise extends BaseModel
{
    public $timestamps = false;

    public const TABLE = 'insurance_franchise';

    protected $table = self::TABLE;

    protected $casts = [
        'active' => 'bool'
    ];

    public function insurances(): belongsToMany
    {
        return $this->belongsToMany(
            Service::class,
            'service_insurance_franchise_relation',
            'franchise_id', 'service_id'
        );
    }

    public function scopeInsuranceService(Builder $query, $insuranceServiceId)
    {
        return $query
            ->with('insurances')
            ->whereHas('insurances', function ($q) use ($insuranceServiceId){
                return $q->where('id', $insuranceServiceId);
            });
    }
}
