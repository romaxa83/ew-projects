<?php

namespace App\Models\Tires;

use App\Contracts\Models\HasModeration;
use App\Filters\Tires\TireFilter;
use App\Models\BaseModel;
use App\Models\Dictionaries\TireRelationshipType;
use App\Models\Dictionaries\TireSpecification;
use App\Models\Inspections\InspectionTire;
use App\Traits\Filterable;
use App\Traits\HasFactory;
use App\Traits\Model\ActiveScopeTrait;
use App\Traits\Model\ModeratedScopeTrait;
use App\Traits\Model\RuleInTrait;
use Database\Factories\Tires\TireFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

/**
 * @method static TireFactory factory()
 */
class Tire extends BaseModel implements Sortable, HasModeration
{
    use SortableTrait;
    use HasFactory;
    use Filterable;
    use ActiveScopeTrait;
    use RuleInTrait;
    use ModeratedScopeTrait;

    public const TABLE = 'tires';

    public const ALLOWED_SORTING_FIELDS = [
        'id',
    ];

    protected $fillable = [
        'serial_number',
        'ogp',
        'specification_id',
        'relationship_type_id',
    ];

    protected $casts = [
        'ogp' => 'float',
    ];

    public function specification(): BelongsTo|TireSpecification
    {
        return $this->belongsTo(TireSpecification::class);
    }

    public function relationshipType(): BelongsTo|TireRelationshipType
    {
        return $this->belongsTo(TireRelationshipType::class);
    }

    public function tireInspections(): HasMany
    {
        return $this->hasMany(InspectionTire::class, 'tire_id', 'id')
            ->orderByDesc('inspection_id');
    }

    public function modelFilter(): string
    {
        return $this->provideFilter(TireFilter::class);
    }

    public function shouldModerated(): bool
    {
        if (!$this->isModerated()) {
            return true;
        }

        return $this->specification->shouldModerated();
    }
}
