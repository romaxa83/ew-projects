<?php

namespace App\Models\Catalog\Features;

use App\Models\BaseModel;
use App\Traits\HasFactory;
use Database\Factories\Catalog\Features\MetricFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Metric
 * @package App\Models\Catalog\Features
 *
 * @method static MetricFactory factory(...$options)
 */
class Metric extends BaseModel
{
    use HasFactory;

    public const TABLE = 'catalog_feature_metrics';

    public $timestamps = false;

    protected $table = self::TABLE;

    protected $fillable = [
        'name'
    ];

    public function values(): HasMany
    {
        return $this->hasMany(Value::class);
    }
}
