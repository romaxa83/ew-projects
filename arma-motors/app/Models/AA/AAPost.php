<?php

namespace App\Models\AA;

use App\Models\BaseModel;
use App\Models\Dealership\Dealership;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * пост в дц (типа пост шиномонтажа и т.д.)
 * пост обслуживает все сервисы (шиномонтаж, кузовной, то)
 *
 * @property int $id
 * @property string $uuid   // id пост в системе AA
 * @property string $name   // название поста
 * @property string $alias  // алиас дц , к которому относиться пост
 *
 * @property-read Dealership|null dealership
 * @property-read AAPostSchedule[]|Collection schedules
 */
class AAPost extends BaseModel
{
    use HasFactory;

    public $timestamps = false;

    public const TABLE = 'aa_posts';
    protected $table = self::TABLE;

    public function dealership(): BelongsTo
    {
        return $this->belongsTo(Dealership::class, "alias", "alias");
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(AAPostSchedule::class, "post_id", "uuid");
    }

    public function orderPlannings(): HasMany
    {
        return $this->hasMany(AAOrderPlanning::class, "post_uuid", "uuid");
    }
}



