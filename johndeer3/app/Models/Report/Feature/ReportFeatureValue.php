<?php

namespace App\Models\Report\Feature;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * таблицы в которой данные по привязаным к отчету характеристикам
 * (поля табличных данных) и их конкретные значения
 *
 * @property int $report_id
 * @property int $feature_id
 * @property int $value_id
 * @property bool $is_sub
 * @property-read ReportValue $value
 * @property-read Feature $feature
 */
class ReportFeatureValue extends Model
{
    public $timestamps = false;

    protected $table = 'report_feature_value_relation';

    protected $casts = [
        'is_sub' => 'boolean',
    ];

    public function value(): HasOne
    {
        return $this->hasOne(ReportValue::class, 'id', 'value_id');
    }

    public function feature(): HasOne
    {
        return $this->hasOne(Feature::class, 'id', 'feature_id');
    }

}
