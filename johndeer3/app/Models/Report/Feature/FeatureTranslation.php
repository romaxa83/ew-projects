<?php

namespace App\Models\Report\Feature;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $feature_id
 * @property string $lang
 * @property string $name
 * @property string|null $unit
 */

class FeatureTranslation extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'feature_translations';
}

