<?php

namespace WezomCms\Catalog\Models\Collections;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $collection_id
 * @property string $name
 * @property string $locale
 * @mixin \Eloquent
 */
class CollectionTranslation extends Model
{
    public $timestamps = false;

    public const TABLE = 'collection_translations';
    protected $table = self::TABLE;

    protected $fillable = ['name'];
}

