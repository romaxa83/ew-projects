<?php

namespace App\Models\Report\Feature;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Report\FeatureEGPivot
 *
 * @property int $feature_id
 * @property int $eg_id
 *
 * @mixin \Eloquent
 */

class FeatureEGPivot extends Model
{
    const TRACTORS = 'tractors';
    const COMBINE = 'combine';
    const SPRAYERS = 'sprayers';

    public $timestamps = false;

    protected $table = 'report_features_eg_pivot';

    public static function tableName()
    {
        return 'report_features_eg_pivot';
    }
}
