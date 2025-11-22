<?php

namespace App\Models\Dictionaries;

use App\Models\BaseTranslates;
use App\Traits\HasFactory;
use Database\Factories\Dictionaries\RecommendationTranslateFactory;

/**
 * @method static RecommendationTranslateFactory factory()
 */
class RecommendationTranslate extends BaseTranslates
{
    use HasFactory;

    public const TABLE = 'recommendation_translates';

    public $timestamps = false;

    protected $fillable = [
        'title',
        'row_id',
        'language',
    ];
}
