<?php

namespace App\Models\Settings\Interstate;

use Illuminate\Database\Eloquent\Model;
use Exception;

/**
 * App\Models\Settings\Interstate\ShuttlePrice
 *
 * @property int $id
 * @property int|null $min
 * @property int $max
 * @property string $price
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|ShuttlePrice newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ShuttlePrice newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ShuttlePrice query()
 * @method static \Illuminate\Database\Eloquent\Builder|ShuttlePrice whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShuttlePrice whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShuttlePrice whereMax($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShuttlePrice whereMin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShuttlePrice wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShuttlePrice whereUpdatedAt($value)
 * @property int|null $division_id
 * @method static \Illuminate\Database\Eloquent\Builder|ShuttlePrice whereDivisionId($value)
 * @mixin \Eloquent
 */
class ShuttlePrice extends Model
{
    protected $table = 'interstate_shuttle_prices';

    protected $fillable = [
        'division_id',
        'min',
        'max',
        'price',
    ];

    public function getRate($volume, $division_id) {
        $data = $this
            ->where('division_id', $division_id)
            ->where('min', '<=' , $volume)
            ->where('max', '>=', $volume)
            ->first(['price']);
        if (!$data) {
            throw new Exception('Shuttle rate for volume "'.$volume.' cbFT" not found!');
        }
        return $data->price;
    }

}
