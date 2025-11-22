<?php

namespace App\Models\Companies;

use App\Foundations\Models\BaseModel;
use Carbon\Carbon;
use Database\Factories\Companies\CompanyFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property int $id
 * @property string name
 * @property Carbon|null created_at
 * @property Carbon|null updated_at
 *
 * @mixin Eloquent
 *
 * @method static CompanyFactory factory(...$parameters)
 */
class Company extends BaseModel
{
    use HasFactory;

    public const TABLE = 'companies';
    protected $table = self::TABLE;

    /** @var array<int, string> */
    protected $fillable = [
        'name',
    ];
}
