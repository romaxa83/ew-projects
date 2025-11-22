<?php

namespace App\Models\BodyShop\Suppliers;

use App\ModelFilters\BodyShop\Suppliers\SupplierFilter;
use App\Models\BodyShop\Inventories\Inventory;
use Eloquent;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * App\Models\BodyShop\Suppliers\Supplier
 *
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string $name
 * @property string|null $url
 *
 * @see Supplier::contacts()
 * @property SupplierContact[] $contacts
 *
 * @see Supplier::inventories()
 * @property Inventory[]|HasMany $inventories
 *
 * @mixin Eloquent
 */
class Supplier extends Model
{
    use Filterable;

    public const TABLE_NAME = 'bs_suppliers';

    protected $table = self::TABLE_NAME;

    /**
     * @var array
     */
    protected $fillable = [
        'name',
        'url',
    ];

    /**
     * @return HasMany|SupplierContact
     */
    public function contacts(): HasMany
    {
        return $this->hasMany(SupplierContact::class, 'supplier_id');
    }

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

    public function inventories(): HasMany
    {
        return $this->hasMany(Inventory::class, 'supplier_id');
    }

    public function hasRelatedEntities(): bool
    {
        return $this->inventories()->exists();
    }
}
