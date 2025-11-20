<?php

namespace App\Models\Report\Feature;

use Illuminate\Database\Eloquent\Model;

/**
 *
 * @property int $feature_id
 * @property int $eg_id
 *
 * @mixin \Eloquent
 */

class FeatureSubEGPivot extends Model
{
    public $timestamps = false;

    protected $table = 'report_features_sub_eg_pivot';

    public static function tableName(): string
    {
        return 'report_features_sub_eg_pivot';
    }
}

