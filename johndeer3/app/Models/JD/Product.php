<?php

namespace App\Models\JD;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property int $jd_id
 * @property int $jd_model_description_id
 * @property int $jd_equipment_group_id
 * @property int $jd_manufacture_id
 * @property int $jd_size_parameter_id
 * @property float $size_name
 * @property boolean $status
 * @property int $type
 * @property string $created_at
 * @property string $updated_at
 * @property-read SizeParameters $sizeParameter
 */

class Product extends BaseModel
{
    const TABLE = 'jd_products';
    protected $table = self::TABLE;

    protected $fillable = [
        'type',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
        'size_name' => 'float',
    ];

    public function scopeActive(Builder $query)
    {
        return $query->where('status', true);
    }

    public function sizeParameter(): BelongsTo
    {
        return $this->belongsTo(SizeParameters::class,'jd_size_parameter_id', 'jd_id');
    }
}
