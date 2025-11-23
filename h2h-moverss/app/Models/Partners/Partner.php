<?php

namespace App\Models\Partners;

use App\Models\Employee;
use App\Models\Truck\Truck;
use Database\Factories\Partners\PartnerFactory;
use Illuminate\Database\Eloquent\{
    Factories\HasFactory,
    Relations\HasMany,
    Model,
    Builder
};
use Illuminate\Support\Carbon;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;


/**
 * App\Models\Partners\Partner
 *
 * @property int id
 * @property string name
 * @property int|null division_id
 * @property string|null contact_person
 * @property string|null phone
 * @property string|null email
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @see self::trucks()
 * @property Truck[]|HasMany trucks
 * @see self::employees()
 * @property Employee[]|HasMany employees
 * @method static PartnerFactory factory(...$parameters)
 * @property-read int|null $trucks_count
 * @method static Builder|Partner newModelQuery()
 * @method static Builder|Partner newQuery()
 * @method static Builder|Partner query()
 * @method static Builder|Partner whereContactPerson($value)
 * @method static Builder|Partner whereCreatedAt($value)
 * @method static Builder|Partner whereDivisionId($value)
 * @method static Builder|Partner whereEmail($value)
 * @method static Builder|Partner whereId($value)
 * @method static Builder|Partner whereName($value)
 * @method static Builder|Partner wherePhone($value)
 * @method static Builder|Partner whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Partner extends Model implements AuditableContract
{
    use AuditableTrait;
    use HasFactory;

    public const TABLE = 'partners';
    protected $table = self::TABLE;

    protected static function newFactory(): PartnerFactory
    {
        return PartnerFactory::new();
    }

    public function trucks(): HasMany
    {
        return $this->hasMany(Truck::class);
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }
}
