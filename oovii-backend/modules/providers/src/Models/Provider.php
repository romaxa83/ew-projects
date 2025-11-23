<?php

namespace WezomCms\Providers\Models;

use Greabock\Tentacles\EloquentTentacle;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Foundation\Auth\User as Authenticated;
use Illuminate\Support\Carbon;
use WezomCms\Catalog\Models\Product;
use WezomCms\Core\Models\Administrator;
use WezomCms\Core\Traits\Model\Filterable;
use WezomCms\Core\Traits\Model\GetForSelectTrait;
use WezomCms\Orders\Models\Order;
use WezomCms\Providers\Types\ProviderStatus;

/**
 * @property int $id
 * @property string $name
 * @property string|null $company
 * @property int $status
 * @property string|null $phone
 * @property bool $phone_verified
 * @property string|null $email
 * @property bool $email_verified
 * @property string $password
 * @property bool $active
 * @property int|null $admin_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property int $region_code
 * @property int $city_code
 * @property string $address
 *
 * @see Provider::adminProfile()
 * @property-read Administrator $adminProfile
 *
 * @see Provider::orders()
 * @property-read Collection|Order[] $orders
 *
 * @see Provider::products()
 * @property-read Collection|Product[] $products
 */
class Provider extends Authenticated
{
    use Filterable;
    use HasFactory;
    use EloquentTentacle;
    use GetForSelectTrait;

    public const TABLE = 'providers';
    protected $table = self::TABLE;

    protected $fillable = [
        'active',
        'status',
        'name',
        'company',
        'email',
        'email_verified',
        'phone',
        'phone_verified',
        'password',
        'region_code',
        'city_code',
        'address',
    ];

    protected $hidden = [
        'password'
    ];

    protected $casts = [
        'active' => 'bool',
        'email_verified' => 'bool',
        'phone_verified' => 'bool'
    ];

    public function isDraft(): bool
    {
        return ProviderStatus::create($this->status)->isDraft();
    }

    public function isModerate(): bool
    {
        return ProviderStatus::create($this->status)->isModerate();
    }

    public function statusRender(): string
    {
        return ProviderStatus::create($this->status)->render();
    }

    public function adminProfile(): BelongsTo
    {
        return $this->belongsTo(Administrator::class, 'admin_id', 'id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function products(): HasManyThrough
    {
        return $this->hasManyThrough(
            Product::class,
            Administrator::class,
            'id',
            'provider_id',
            'admin_id',
            'id'
        );
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('active', true);
    }
}
