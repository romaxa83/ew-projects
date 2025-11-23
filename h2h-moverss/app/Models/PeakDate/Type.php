<?php

namespace App\Models\PeakDate;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\PeakDate\Type
 *
 * @property int $id
 * @property string $title
 * @property string|null $color
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property string|null $deleted_at
 * @method static \Illuminate\Database\Eloquent\Builder|Type newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Type newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Type query()
 * @method static \Illuminate\Database\Eloquent\Builder|Type whereColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Type whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Type whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Type whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Type whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Type whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Type extends Model
{
    protected $table = 'peaks_types';
}
