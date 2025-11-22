<?php

namespace App\Models\Alerts;

use App\Filters\Alerts\AlertFilter;
use App\Models\BaseModel;
use App\Traits\Filterable;
use App\Traits\HasFactory;
use Database\Factories\Alerts\AlertFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @method static AlertFactory factory(...$parameters)
 */
class Alert extends BaseModel
{
    use HasFactory;
    use Filterable;

    protected $fillable = [
        'title',
        'description',
        'meta',
        'type',
        'model_id',
        'model_type',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'meta' => 'array',
        'model_id' => 'int'
    ];

    public function model(): MorphTo
    {
        return $this->morphTo();
    }

    public function recipients(): HasMany
    {
        return $this->hasMany(AlertRecipient::class);
    }

    public function modelFilter(): string
    {
        return AlertFilter::class;
    }
}
