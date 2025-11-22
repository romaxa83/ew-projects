<?php

namespace App\Models\Suppliers;

use App\Foundations\Models\BaseModel;
use App\Foundations\Traits\Filters\Filterable;
use App\ModelFilters\Suppliers\SupplierFilter;
use App\Models\Inventories\Inventory;
use Database\Factories\Suppliers\SupplierFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string name
 * @property string|null url
 * @property int|null origin_id
 * @property Carbon|null created_at
 * @property Carbon|null updated_at
 *
 * @see Supplier::contacts()
 * @property SupplierContact[] $contacts
 *
 * @see Supplier::inventories()
 * @property Inventory[]|HasMany $inventories
 *
 * @mixin Eloquent
 *
 * @method static SupplierFactory factory(...$parameters)
 */
class Supplier extends BaseModel
{
    use Filterable;
    use HasFactory;

    public const TABLE = 'suppliers';
    protected $table = self::TABLE;

    /** @var array<int, string> */
    protected $fillable = [
        'name',
        'url',
    ];

    public function mainContact(): ?SupplierContact
    {
        return $this->contacts()->main()->count()
            ? $this->contacts()->main()->first()
            : $this->contacts()->first();
    }

    public function modelFilter()
    {
        return $this->provideFilter(SupplierFilter::class);
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(SupplierContact::class, 'supplier_id');
    }

    public function inventories(): HasMany
    {
        return $this->hasMany(Inventory::class, 'supplier_id');
    }

    public function hasRelatedEntities(): bool
    {
        return $this->inventories()->exists();
    }
}

