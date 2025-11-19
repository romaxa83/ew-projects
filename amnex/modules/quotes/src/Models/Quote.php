<?php

namespace Wezom\Quotes\Models;

use App\Enums\DateFormatEnum;
use Barryvdh\LaravelIdeHelper\Eloquent;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Wezom\Core\Traits\Model\Filterable;
use Wezom\Quotes\Enums\ContainerDimensionTypeEnum;
use Wezom\Quotes\Enums\QuoteStatusEnum;

/**
 * @property int $id
 * @property int|null $user_id
 * @property int $pickup_terminal_id
 * @property float $mileage_cost
 * @property float $cargo_cost
 * @property float $storage_cost
 * @property float $total
 * @property QuoteStatusEnum $status
 * @property ContainerDimensionTypeEnum $container_type
 * @property string|null $container_number
 * @property boolean $is_not_standard_dimension
 * @property boolean $is_transload
 * @property boolean $is_palletized
 * @property int|null $number_pallets
 * @property int|null $piece_count
 * @property int $days_stored
 * @property string|null $email
 * @property string|null $phone
 * @property string|null $user_name
 * @property CarbonImmutable|null $quote_accepted_at
 * @property CarbonImmutable|null $updated_at
 * @property CarbonImmutable|null $created_at
 * @property int|null $terminal_distance_id
 * @property array|null $payload
 * @property string $delivery_address
 * @method static Builder|Quote newModelQuery()
 * @method static Builder|Quote newQuery()
 * @method static Builder|Quote query()
 * @method static Builder|Quote whereCreatedAt($value)
 * @method static Builder|Quote whereId($value)
 * @mixin Eloquent
 *
 * @see self::pickupTerminal()
 * @property-read Terminal $pickupTerminal
 *
 * @see self::distance()
 * @property-read TerminalDistance $distance
 */
class Quote extends Model
{
    use HasFactory;
    use Filterable;
    use SoftDeletes;

    public const TABLE = 'quotes';

    protected $fillable = [
        'terminal_distance_id',
        'mileage_cost',
        'cargo_cost',
        'storage_cost',
        'total',
        'payload'
    ];

    protected $casts = [
        'status' => QuoteStatusEnum::class,
        'container_type' => ContainerDimensionTypeEnum::class,
        'is_not_standard_dimension' => 'boolean',
        'is_transload' => 'boolean',
        'is_palletized' => 'boolean',
        'mileage_cost' => 'float',
        'cargo_cost' => 'float',
        'storage_cost' => 'float',
        'total' => 'float',
        'payload' => 'array',
        'quote_accepted_at' => 'datetime',
    ];

    public function pickupTerminal(): BelongsTo
    {
        return $this->belongsTo(Terminal::class, 'pickup_terminal_id', 'id');
    }

    public function distance(): BelongsTo
    {
        return $this->belongsTo(TerminalDistance::class, 'terminal_distance_id', 'id');
    }

    public function convertDateQuoteAccepted(Quote $model): ?string
    {
        return $model->quote_accepted_at?->setTimezone(DateFormatEnum::CLIENT_TZ->value);
    }

    public function convertDateCreated(Quote $model): ?string
    {
        return $model->created_at?->setTimezone(DateFormatEnum::CLIENT_TZ->value);
    }

    public function convertDateUpdated(Quote $model): ?string
    {
        return $model->updated_at?->setTimezone(DateFormatEnum::CLIENT_TZ->value);
    }
}
