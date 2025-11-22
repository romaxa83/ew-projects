<?php

namespace App\Models\Dictionaries;

use App\Models\BaseTranslates;
use App\Traits\HasFactory;
use Database\Factories\Dictionaries\ProblemTranslateFactory;

/**
 * @method static ProblemTranslateFactory factory()
 */
class ProblemTranslate extends BaseTranslates
{
    use HasFactory;

    public const TABLE = 'problem_translates';

    public $timestamps = false;

    protected $fillable = [
        'title',
        'row_id',
        'language',
    ];
}
