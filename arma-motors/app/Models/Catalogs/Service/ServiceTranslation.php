<?php

namespace App\Models\Catalogs\Service;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property $service_id
 * @property string $lang
 * @property string $name
 *
 */

class ServiceTranslation extends Model
{
    public $timestamps = false;

    public const TABLE = 'service_translations';

    protected $table = self::TABLE;
}
