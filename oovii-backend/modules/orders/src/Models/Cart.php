<?php

namespace WezomCms\Orders\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * \WezomCms\Orders\Models\Cart
 *
 * @property int $id
 * @property string $hash
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property array|null $conditions
 * @property array|null $delivery_data
 * @property-read Collection|CartItem[] $items
 * @method static Builder|Cart newModelQuery()
 * @method static Builder|Cart newQuery()
 * @method static Builder|Cart query()
 * @method static Builder|Cart whereConditions($value)
 * @method static Builder|Cart whereCreatedAt($value)
 * @method static Builder|Cart whereHash($value)
 * @method static Builder|Cart whereId($value)
 * @method static Builder|Cart whereUpdatedAt($value)
 * @mixin Eloquent
 */
class Cart extends Model
{
    use HasFactory;

    public const TABLE = 'carts';

    protected $table = self::TABLE;

    /**
     * The model's attributes.
     *
     * @var array
     */
    protected $attributes = [
        'conditions' => '[]',
        'delivery_data' => '[]',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['hash'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'conditions' => 'array',
        'delivery_data' => 'array',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }
}
