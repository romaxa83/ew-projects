<?php

namespace App\Models\Dictionaries;

use App\Models\BaseTranslates;
use App\Traits\HasFactory;
use Database\Factories\Dictionaries\InspectionReasonTranslateFactory;

/**
 * @method static InspectionReasonTranslateFactory factory()
 */
class InspectionReasonTranslate extends BaseTranslates
{
    use HasFactory;

    public const TABLE = 'inspection_reason_translates';

    public $timestamps = false;

    protected $fillable = [
        'title',
        'row_id',
        'language',
    ];
}
