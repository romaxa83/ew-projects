<?php

namespace App\Models\Locations;

use App\Models\BaseTranslates;

class RegionTranslate extends BaseTranslates
{
    public const TABLE = 'region_translates';

    public $incrementing = false;

    public $timestamps = false;

    public $primaryKey = ['row_id', 'language'];

    protected $fillable = [
        'title',
        'row_id',
        'language',
    ];
}
