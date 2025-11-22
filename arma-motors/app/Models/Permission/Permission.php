<?php

namespace App\Models\Permission;

use App\Traits\HasFactory;
use Database\Factories\Permission\PermissionFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int id
 * @property string name
 * @property string guard_name
 *
 * @method static PermissionFactory factory()
 */
class Permission extends \Spatie\Permission\Models\Permission
{
    use HasFactory;

    public const TABLE_NAME = 'permissions';

    // relations
    public function translations(): HasMany
    {
        return $this->hasMany(PermissionTranslation::class);
    }

    public function current(): HasOne
    {
        return $this->hasOne(PermissionTranslation::class)->where('lang', \App::getLocale());
    }
}

