<?php

namespace App\Models\User;

use App\Helpers\ConvertLangToLocale;
use App\Models\Translate;
use App\Traits\ActiveTrait;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $role
 */

class Role extends Model
{
    use ActiveTrait;

    public $timestamps = false;

    const ROLE_ADMIN = 'admin';
    const ROLE_TM = 'tm';
    const ROLE_SM = 'sm';
    const ROLE_PS = 'ps';
    const ROLE_PSS = 'pss';
    const ROLE_TMD = 'tmd'; //роль как у tm, только можно создавать в приложении, и не синхранизироват с boed

    protected $table = 'users_roles';

    public static function getRoles()
    {
        return [
            self::ROLE_ADMIN,
            self::ROLE_SM,
            self::ROLE_TM,
            self::ROLE_PS,
            self::ROLE_PSS,
            self::ROLE_TMD
        ];
    }

    public function isPs()
    {
        return $this->role == self::ROLE_PS;
    }

    public function current()
    {
        return $this->translate()->where('lang', ConvertLangToLocale::convert(\App::getLocale()));
    }

    public function translate()
    {
        return $this->morphMany(Translate::class, 'entity');
    }

    public function users()
    {
        return $this->belongsToMany( User::class);
    }
}
