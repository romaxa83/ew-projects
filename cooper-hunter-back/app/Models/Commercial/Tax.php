<?php

namespace App\Models\Commercial;

use App\Casts\PriceCast;
use App\Models\BaseModel;
use App\Traits\HasFactory;

/**
 * @property integer id
 * @property string guid
 * @property string|null name
 * @property float value
 */
class Tax extends BaseModel
{
    use HasFactory;

    public $timestamps = false;

    public const TABLE = 'taxes';
    protected $table = self::TABLE;

    protected $casts = [
        'value' => PriceCast::class,
    ];
}


