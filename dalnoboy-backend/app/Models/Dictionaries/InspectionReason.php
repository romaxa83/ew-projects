<?php

namespace App\Models\Dictionaries;

use App\Filters\Dictionaries\InspectionReasonFilter;
use App\Models\BaseModel;
use App\Traits\Filterable;
use App\Traits\HasFactory;
use App\Traits\Model\ActiveScopeTrait;
use App\Traits\Model\HasTranslations;
use App\Traits\Model\RuleInTrait;
use Database\Factories\Dictionaries\InspectionReasonFactory;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

/**
 * @method static InspectionReasonFactory factory()
 */
class InspectionReason extends BaseModel implements Sortable
{
    use HasTranslations;
    use SortableTrait;
    use HasFactory;
    use Filterable;
    use ActiveScopeTrait;
    use RuleInTrait;

    public const TABLE = 'inspection_reasons';

    public const ALLOWED_SORTING_FIELDS = [
        'id',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    public function modelFilter(): string
    {
        return $this->provideFilter(InspectionReasonFilter::class);
    }
}
