<?php

namespace App\Models\Commercial;

use App\Enums\Formats\DatetimeEnum;
use App\Models\BaseModel;
use App\Traits\Filterable;
use App\Traits\HasFactory;
use Carbon\Carbon;
use Database\Factories\Commercial\CommercialProjectAdditionFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int id
 * @property int commercial_project_id
 * @property string|null installer_license_number
 * @property string|null purchase_place
 * @property Carbon|null purchase_date
 * @property Carbon|null installation_date
 *
 * @see CommercialProjectAddition::project()
 * @property-read CommercialProject project
 *
 * @method static CommercialProjectAdditionFactory factory(...$parameters)
 */
class CommercialProjectAddition extends BaseModel
{
    use HasFactory;
    use Filterable;

    public $timestamps = false;

    public const TABLE = 'commercial_project_additions';
    protected $table = self::TABLE;

    protected $casts = [
        'purchase_date' => DatetimeEnum::DEFAULT,
        'installation_date' => DatetimeEnum::DEFAULT,
    ];

    protected $dates = [
        'purchase_date',
        'installation_date',
    ];

    protected $appends = [
        'can_update'
    ];

    public function project(): BelongsTo|CommercialProject
    {
        return $this->belongsTo(CommercialProject::class, 'commercial_project_id', 'id');
    }

    public function getCanUpdateAttribute(): bool
    {
        return $this->project->warranty ? false: true;
    }
}
