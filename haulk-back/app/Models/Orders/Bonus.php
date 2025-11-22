<?php

namespace App\Models\Orders;

use App\Collections\Models\Orders\BonusCollection;
use App\Models\DiffableInterface;
use App\Traits\Diffable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $type
 * @property float $price
 * @property string $to
 */
class Bonus extends Model implements DiffableInterface
{
    use HasFactory;
    use Diffable;

    public const TABLE_NAME = 'bonuses';

    protected $fillable = [
        'type',
        'price',
        'to',
    ];

    protected $casts = [
        'price' => 'float'
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }

    public function newCollection(array $models = []): BonusCollection
    {
        return BonusCollection::make($models);
    }
}
