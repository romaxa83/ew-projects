<?php

namespace App\Models\Locations;

use App\Filters\Locations\RegionFilter;
use App\Models\BaseModel;
use App\Traits\Filterable;
use App\Traits\Model\HasTranslations;
use App\Traits\Model\SetSortAfterCreate;

class Region extends BaseModel
{
    use HasTranslations;
    use SetSortAfterCreate;
    use Filterable;

    public const TABLE = 'regions';

    public const ALLOWED_TRANSLATE_FIELDS = [
        'title'
    ];

    public $timestamps = false;

    public function modelFilter(): string
    {
        return $this->provideFilter(RegionFilter::class);
    }
}
