<?php

namespace App\Models\Dictionaries;

use App\Models\BaseTranslates;
use App\Traits\HasFactory;
use Database\Factories\Dictionaries\TireRelationshipTypeTranslateFactory;

/**
 * @method static TireRelationshipTypeTranslateFactory factory()
 */
class TireRelationshipTypeTranslate extends BaseTranslates
{
    use HasFactory;

    public const TABLE = 'tire_relationship_type_translates';

    public $timestamps = false;

    protected $fillable = [
        'title',
        'row_id',
        'language',
    ];
}
