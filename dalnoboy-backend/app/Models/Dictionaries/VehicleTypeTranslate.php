<?php

namespace App\Models\Dictionaries;

use App\Models\BaseTranslates;
use App\Traits\HasFactory;
use Database\Factories\Dictionaries\VehicleTypeTranslateFactory;

/**
 * @method static VehicleTypeTranslateFactory factory()
 */
class VehicleTypeTranslate extends BaseTranslates
{
    use HasFactory;

    public const TABLE = 'vehicle_type_translates';

    public $timestamps = false;

    protected $fillable = [
        'title',
        'row_id',
        'language',
    ];
}
