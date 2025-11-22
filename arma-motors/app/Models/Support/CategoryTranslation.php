<?php

namespace App\Models\Support;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property $category_id
 * @property string $lang
 * @property string|null $name
 *
 */

class CategoryTranslation extends Model
{
    use HasFactory;

    public $timestamps = false;

    public const TABLE = 'support_category_translations';

    protected $table = self::TABLE;
}

