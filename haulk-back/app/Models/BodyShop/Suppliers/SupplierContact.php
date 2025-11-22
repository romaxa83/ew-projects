<?php

namespace App\Models\BodyShop\Suppliers;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * App\Models\BodyShop\Suppliers\SupplierContact
 *
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string $name
 * @property string $phone
 * @property array|null $phones
 * @property string|null $phone_extension
 * @property string $email
 * @property array|null $emails
 * @property string|null $position
 * @property  bool $is_main
 *
 * @see SupplierContact::supplier()
 * @property Supplier $supplier
 *
 * @see SupplierContact::scopeMain()
 * @method static Builder|SupplierContact main()
 *
 * @mixin Eloquent
 */
class SupplierContact extends Model
{
    public const TABLE_NAME = 'bs_supplier_contacts';

    protected $table = self::TABLE_NAME;

    /** @var array  */
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

    /** @var array */
    protected $casts = [
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
