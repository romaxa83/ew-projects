<?php

namespace App\Models\Promotion;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property $model_id
 * @property string $lang
 * @property string|null $name
 * @property string|null $text
 *
 */

class PromotionTranslation extends Model
{
    use HasFactory;

    public $timestamps = false;

    public const TABLE = 'promotion_translations';

    protected $table = self::TABLE;
}
