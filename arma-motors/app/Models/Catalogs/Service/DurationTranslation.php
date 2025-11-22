<?php

namespace App\Models\Catalogs\Service;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property $duration_id
 * @property string $lang
 * @property string $name
 *
 */

class DurationTranslation extends Model
{
    public $timestamps = false;

    public const TABLE = 'service_duration_translations';

    protected $table = self::TABLE;
}

