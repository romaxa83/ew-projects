<?php

namespace App\Models\Sips;

use App\Filters\Sips\SipFilter;
use App\Models\BaseModel;
use App\Models\Employees\Employee;
use App\Traits\Filterable;
use App\Traits\HasFactory;
use Carbon\Carbon;
use Database\Factories\Sips\SipFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int id
 * @property string number
 * @property string password
 * @property Carbon created_at
 * @property Carbon updated_at
 *
 * @see Sip::employee()
 * @property-read Employee employee
 *
 * @method static SipFactory factory(int $number = null)
 */
class Sip extends BaseModel
{
    use HasFactory;
    use Filterable;

    protected $table = self::TABLE;
    public const TABLE = 'sips';

    public const MIN_LENGTH_PASSWORD = 10;

    protected $fillable = [];

    public function modelFilter(): string
    {
        return SipFilter::class;
    }

    public function employee(): HasOne
    {
        return $this->hasOne(Employee::class);
    }
}
