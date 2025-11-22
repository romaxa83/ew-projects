<?php

namespace App\Models\Catalogs\Service;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property bool $active
 * @property int $sort
 *
 */
class Privileges extends BaseModel
{

    public $timestamps = false;

    public const TABLE = 'privileges';

    protected $table = self::TABLE;

    protected $casts = [
        'active' => 'bool'
    ];

    public function translations(): HasMany
    {
        return $this->hasMany(PrivilegesTranslation::class, 'privileges_id', 'id');
    }
    public function current(): HasOne
    {
        return $this->hasOne(PrivilegesTranslation::class,'privileges_id', 'id')->where('lang', \App::getLocale());
    }
}
