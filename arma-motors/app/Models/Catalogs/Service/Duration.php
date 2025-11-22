<?php

namespace App\Models\Catalogs\Service;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property bool $active
 * @property int $sort
 *
 */
class Duration extends BaseModel
{
    public $timestamps = false;

    public const TABLE = 'service_durations';

    protected $table = self::TABLE;

    protected $casts = [
        'active' => 'bool'
    ];

    public function services(): belongsToMany
    {
        return $this->belongsToMany(
            Service::class,
            'service_duration_service_relation',
            'duration_id', 'service_id'
        );
    }

    public function translations(): HasMany
    {
        return $this->hasMany(DurationTranslation::class, 'duration_id', 'id');
    }
    public function current(): HasOne
    {
        return $this->hasOne(DurationTranslation::class,'duration_id', 'id')->where('lang', \App::getLocale());
    }

    public function scopeByService(Builder $query, $serviceId)
    {
        return $query
            ->with('services')
            ->whereHas('services', function ($q) use ($serviceId){
                return $q->where('id', $serviceId);
            });
    }
}

