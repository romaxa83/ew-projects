<?php

namespace WezomCms\Catalog\Models\Labels;

use Eloquent;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $label_id
 * @property string $name
 * @property string $locale
 * @mixin Eloquent
 */
class LabelTranslation extends Model
{
    public $timestamps = false;

    public const TABLE = 'label_translations';
    protected $table = self::TABLE;

    protected $fillable = ['name'];
}
