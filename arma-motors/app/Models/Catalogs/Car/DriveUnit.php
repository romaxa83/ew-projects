<?php

namespace App\Models\Catalogs\Car;

use App\Models\BaseModel;

/**
 * @property int $id
 * @property bool $active
 * @property int $sort
 * @property string $name
 *
 */
class DriveUnit extends BaseModel
{
    public $timestamps = false;

    public const TABLE = 'drive_units';

    protected $table = self::TABLE;

    protected $casts = [
        'active' => 'bool'
    ];
}
