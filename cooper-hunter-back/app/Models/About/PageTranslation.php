<?php

namespace App\Models\About;

use App\Models\BaseTranslation;

class PageTranslation extends BaseTranslation
{
    public const TABLE = 'page_translations';

    public $timestamps = false;

    protected $fillable = [
        'title',
        'description',
        'row_id',
        'language',
    ];
}
