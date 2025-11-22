<?php

namespace App\Models\Tags;

use Illuminate\Database\Eloquent\Relations\MorphPivot;

/**
 * @property int tag_id
 * @property int taggable_id
 * @property string taggable_type
 */
class Taggable extends MorphPivot
{
    public const TABLE = 'taggables';
    protected $table = self::TABLE;
}
