<?php

namespace App\Models\Report\Feature;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Report\Feature
 *
 * @property int $id
 * @property string $value
 * @property string|null $model_description_name
 * @property int|null $model_description_id
 * @property int|null $value_id
 */

class ReportValue extends Model
{
    public $timestamps = false;

    protected $table = 'report_feature_values';

    public function valueCurrent()
    {
        return $this->hasOne(FeatureValueTranslates::class, 'value_id', 'value_id')
            ->where('lang', \App::getLocale());
    }
}
