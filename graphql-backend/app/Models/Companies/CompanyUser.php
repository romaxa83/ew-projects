<?php

namespace App\Models\Companies;

use App\Models\BaseModel;
use App\Models\Users\User;
use App\Traits\HasFactory;
use Database\Factories\Users\CompanyUserFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int company_id
 * @property int user_id
 * @property string state
 * @method static CompanyUserFactory factory()
 */
class CompanyUser extends BaseModel
{
    use HasFactory;

    public const TABLE = 'company_user';

    public $incrementing = false;

    public $timestamps = false;

    protected $primaryKey = null;

    protected $table = self::TABLE;

    protected $fillable = [
        'user_id',
        'company_id',
        'state'
    ];

    protected static function newFactory(): CompanyUserFactory
    {
        return new CompanyUserFactory();
    }

    public function user(): BelongsTo|User
    {
        return $this->belongsTo(User::class);
    }

    public function company(): BelongsTo|Company
    {
        return $this->belongsTo(Company::class);
    }
}
