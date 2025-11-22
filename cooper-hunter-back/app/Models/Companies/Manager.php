<?php

namespace App\Models\Companies;

use App\Casts\EmailCast;
use App\Casts\PhoneCast;
use App\Models\BaseModel;
use App\Traits\HasFactory;
use App\ValueObjects\Email;
use App\ValueObjects\Phone;
use Database\Factories\Companies\ManagerFactory;

/**
 * @property integer id
 * @property integer company_id
 * @property string name
 * @property Email email
 * @property Phone phone
 *
 * @method static ManagerFactory factory(...$options)
 */
class Manager extends BaseModel
{
    use HasFactory;

    public $timestamps = false;

    public const TABLE = 'company_managers';
    protected $table = self::TABLE;

    protected $casts = [
        'email' => EmailCast::class,
        'phone' => PhoneCast::class,
    ];
}
