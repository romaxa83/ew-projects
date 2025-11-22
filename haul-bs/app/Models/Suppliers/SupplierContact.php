<?php

namespace App\Models\Suppliers;

use App\Foundations\Casts\Contact\EmailCast;
use App\Foundations\Casts\Contact\PhoneCast;
use App\Foundations\Models\BaseModel;
use App\Foundations\ValueObjects\Email;
use App\Foundations\ValueObjects\Phone;
use Database\Factories\Suppliers\SupplierContactFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int id
 * @property string name
 * @property Phone phone
 * @property array|null phones
 * @property string|null phone_extension
 * @property Email email
 * @property array|null emails
 * @property string|null position
 * @property bool is_main
 * @property int supplier_id
 * @property Carbon|null created_at
 * @property Carbon|null updated_at
 *
 * @see SupplierContact::supplier()
 * @property Supplier $supplier
 *
 * @see SupplierContact::scopeMain()
 * @method static Builder|SupplierContact main()
 *
 * @mixin Eloquent
 *
 * @method static SupplierContactFactory factory(...$parameters)
 */
class SupplierContact extends BaseModel
{
    use HasFactory;

    public const TABLE = 'supplier_contacts';

    protected $table = self::TABLE;

    /** @var array<int, string>  */
    protected $fillable = [
        'name',
        'phone',
        'phone_extension',
        'phones',
        'email',
        'emails',
        'position',
        'is_main',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'email' => EmailCast::class,
        'phone' => PhoneCast::class,
        'phones' => 'array',
        'emails' => 'array',
        'is_main' => 'boolean',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function scopeMain(Builder $builder): Builder
    {
        return $builder->where('is_main', true);
    }
}
