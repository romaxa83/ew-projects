<?php

namespace App\Models\Permission;

use App\Traits\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PermissionTranslation extends Model
{
    use HasFactory;

    public $timestamps = false;

    public const TABLE = 'permission_translations';

    protected $table = 'permission_translations';
}
