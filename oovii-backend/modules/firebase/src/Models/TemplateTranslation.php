<?php

namespace WezomCms\Firebase\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $template_id
 * @property string $locale
 * @property string $title
 * @property string $text
 */

class TemplateTranslation extends Model
{
    public $timestamps = false;

    public const TABLE = 'fcm_template_translations';
    protected $table = self::TABLE;

    protected $fillable = ['title', 'text'];
}
