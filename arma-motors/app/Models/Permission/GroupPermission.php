<?php

namespace App\Models\Permission;

use App\Traits\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int id
 * @property int sort
 * @property string name
 */
class GroupPermission extends Model
{
    use HasFactory;

    public $timestamps = false;

    public const TABLE_NAME = 'permission_groups';

    protected $table = 'permission_groups';

    // relation
    public function permissions(): HasMany
    {
        return $this->hasMany(Permission::class, 'group_id', 'id');
    }

    public function translations(): HasMany
    {
        return $this->hasMany(GroupPermissionTranslation::class, 'group_id', 'id');
    }

    public function current(): HasOne
    {
        return $this->hasOne(GroupPermissionTranslation::class,'group_id', 'id')->where('lang', \App::getLocale());
    }
}
