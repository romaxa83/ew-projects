<?php

namespace App\Models\Catalog\Tickets;

use App\Enums\Tickets\TicketStatusEnum;
use App\Filters\Catalog\Tickets\TicketFilter;
use App\Models\BaseHasTranslation;
use App\Models\Catalog\Products\Product;
use App\Models\Catalog\Products\ProductSerialNumber;
use App\Models\Orders\Categories\OrderCategory;
use App\Models\Orders\Order;
use App\Traits\Filterable;
use App\Traits\HasFactory;
use BenSampo\Enum\Traits\CastsEnums;
use Database\Factories\Catalog\Tickets\TicketFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Support\Carbon;

/**
 * @property int id
 * @property string serial_number
 * @property string guid
 * @property string status
 * @property null|string code
 * @property array order_parts
 * @property Carbon created_at
 * @property Carbon updated_at
 * @property null|int case_id
 *
 * @see Ticket::orderPartsRelation()
 * @property-read Collection|OrderCategory[] orderPartsRelation
 *
 * @method static TicketFactory factory(...$parameters)
 */
class Ticket extends BaseHasTranslation
{
    use Filterable;
    use HasFactory;
    use CastsEnums;

    public const TABLE = 'tickets';

    protected $table = self::TABLE;

    protected $casts = [
        'status' => TicketStatusEnum::class,
        'order_parts' => 'array',
    ];

    public function modelFilter(): string
    {
        return TicketFilter::class;
    }

    public function orderPartsRelation(): BelongsToMany|OrderCategory
    {
        return $this->belongsToMany(OrderCategory::class, 'ticket_order_category')
            ->withPivot(['quantity', 'description']);
    }

    public function orders(): HasMany|Order
    {
        return $this->hasMany(Order::class);
    }

    public function product(): HasOneThrough
    {
        return $this->hasOneThrough(
            Product::class,
            ProductSerialNumber::class,
            'serial_number',
            'id',
            'serial_number',
            'product_id'
        );
    }
}
