<?php

namespace App\Models\Dictionaries;

use App\Models\BaseModel;
use App\Traits\Model\HasTranslations;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

class TireChangesReason extends BaseModel implements Sortable
{
    use HasTranslations;
    use SortableTrait;

    public const TABLE = 'tire_changes_reasons';

    public $timestamps = false;

    public $fillable = [
        'need_description',
    ];
}
