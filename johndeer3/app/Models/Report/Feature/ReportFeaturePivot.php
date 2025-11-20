<?php

namespace App\Models\Report\Feature;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Report\ReportFeatureValuePivot
 *
 * @property int $report_id
 * @property int $feature_id
 * @property string $value
 */

class ReportFeaturePivot extends Model
{
    public $timestamps = false;

    protected $table = 'reports_features_pivot';

    public static function tableName(): string
    {
        return 'reports_features_pivot';
    }
}
