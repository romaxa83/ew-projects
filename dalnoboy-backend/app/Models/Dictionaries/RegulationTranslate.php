<?php

namespace App\Models\Dictionaries;

use App\Models\BaseTranslates;
use App\Traits\HasFactory;
use Database\Factories\Dictionaries\RegulationFactory;

/**
 * @method static RegulationFactory factory()
 */
class RegulationTranslate extends BaseTranslates
{
    use HasFactory;

    public const TABLE = 'regulation_translates';

    public $timestamps = false;

    protected $fillable = [
        'title',
        'row_id',
        'language',
    ];
}
