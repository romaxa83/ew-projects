<?php

namespace App\Models\Dictionaries;

use App\Models\BaseTranslates;

class TireChangesReasonTranslate extends BaseTranslates
{
    public const TABLE = 'tire_changes_reason_translates';

    public $timestamps = false;

    protected $fillable = [
        'title',
        'row_id',
        'language',
    ];
}
