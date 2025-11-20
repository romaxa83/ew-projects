<?php

namespace App\Models\Report\Feature;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $value_id
 * @property string $lang
 * @property string $name
 */

class FeatureValueTranslates extends Model
{
    use HasFactory;

    public $timestamps = false;

    const TABLE = 'feature_value_translates';
    protected $table = self::TABLE;
}
