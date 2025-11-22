<?php

namespace App\Models\Permission;

use App\Traits\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int id
 * @property int role_id
 * @property string lang
 * @property string name
 * @property string created_at
 * @property string updated_at
 *
 * @method static static|Builder query()
 */
class RoleTranslation extends Model
{
    use HasFactory;

    public $timestamps = false;

    public const TABLE = 'roles_translations';

    protected $table = 'roles_translations';
}
