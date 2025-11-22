<?php

namespace App\Models\Dictionaries;

use App\Models\BaseTranslates;
use App\Traits\HasFactory;
use Database\Factories\Dictionaries\VehicleClassTranslateFactory;

/**
 * @method static VehicleClassTranslateFactory factory()
 */
class VehicleClassTranslate extends BaseTranslates
{
    use HasFactory;

    public const TABLE = 'vehicle_class_translates';

    public $timestamps = false;

    protected $fillable = [
        'title',
        'row_id',
        'language',
    ];
}
