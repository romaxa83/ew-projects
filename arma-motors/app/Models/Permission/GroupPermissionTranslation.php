<?php

namespace App\Models\Permission;

use Illuminate\Database\Eloquent\Model;

class GroupPermissionTranslation extends Model
{
    public $timestamps = false;

    public const TABLE = 'permission_group_translations';

    protected $table = 'permission_group_translations';
}
