<?php

namespace Wezom\Quotes\Models;

use Barryvdh\LaravelIdeHelper\Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $pickup_terminal_id
 * @property string $delivery_address
 * @property float $distance_as_mile
 * @property float $distance_as_meters
 * @property string|null $distance_text
 * @property array $start_location
 * @property array $end_location
 * @property array $delivery_data
 * @method static Builder|Terminal newModelQuery()
 * @method static Builder|Terminal newQuery()
 * @method static Builder|Terminal query()
 * @method static Builder|Terminal whereCreatedAt($value)
 * @method static Builder|Terminal whereId($value)
 * @mixin Eloquent
 */
class TerminalDistance extends Model
{
    use HasFactory;

    public $timestamps = false;

    public const TABLE = 'terminal_distances';

    protected $fillable = [];
    protected $casts = [
        'distance_as_mile' => 'float',
        'distance_as_meters' => 'float',
        'start_location' => 'array',
        'end_location' => 'array',
        'delivery_data' => 'array',
    ];
}
