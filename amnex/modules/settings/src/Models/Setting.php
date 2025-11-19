<?php

namespace Wezom\Settings\Models;

use Barryvdh\LaravelIdeHelper\Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $key
 * @property string $value
 * @property string|null $title
 * @property string|null $group_title
 * @property string|null $type
 * @method static Builder|Setting newModelQuery()
 * @method static Builder|Setting newQuery()
 * @method static Builder|Setting query()
 * @method static Builder|Setting whereCreatedAt($value)
 * @method static Builder|Setting whereId($value)
 * @mixin Eloquent
 */
class Setting extends Model
{
    use HasFactory;

    public const TABLE = 'settings';
    public const GROUP_MILEAGE_RATE = 'Mileage rate';
    public const GROUP_ACCESORIAL = 'Accessorial';
    public const GROUP_ACCESORIALS = 'Accessorials';
    public const GROUP_STORAGE = 'Storage';
    public const KEY_DAYS_TO_EXPIRE = 'days_to_expired';
    public const KEY_RATE_0_20_MILES = 'rate_0_20_miles';
    public const KEY_RATE_20_40_MILES = 'rate_20_40_miles';
    public const KEY_RATE_40_60_MILES = 'rate_40_60_miles';
    public const KEY_RATE_60_80_MILES = 'rate_60_80_miles';
    public const KEY_RATE_80_100_MILES = 'rate_80_100_miles';
    public const KEY_FURTHER_MILES = 'further_miles';
    public const KEY_FURTHER_RATE = 'further_rate';
    public const KEY_PRICE_FOR_PALLET = 'price_for_pallet';
    public const KEY_PRICE_FOR_PIECE = 'price_for_piece';
    public const KEY_PRICE_FOR_STORAGE = 'price_for_storage';
    public const TYPE_INT = 'int';
    public const TYPE_FLOAT = 'float';
    public const TYPE_STRING = 'string';

    public $timestamps = false;
    protected $fillable = [
        'key',
        'value',
        'type'
    ];
    protected $casts = [];
}
