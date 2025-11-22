<?php

namespace App\Models\Catalog\Solutions\Series;

use App\Models\BaseTranslation;

class SolutionSeriesTranslation extends BaseTranslation
{
    public $timestamps = false;

    protected $fillable = [
        'title',
        'description',
        'language',
        'row_id',
    ];
}
