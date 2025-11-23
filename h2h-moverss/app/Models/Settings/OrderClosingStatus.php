<?php

namespace App\Models\Settings;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Settings\OrderClosingStatus
 *
 * @property int $id
 * @property string $title
 * @property int|null $group_id
 * @property int $sort
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property string|null $deleted_at
 * @property-read \App\Models\Settings\OrderClosingStatusGroup|null $group
 * @method static \Illuminate\Database\Eloquent\Builder|OrderClosingStatus newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OrderClosingStatus newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OrderClosingStatus query()
 * @method static \Illuminate\Database\Eloquent\Builder|OrderClosingStatus whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderClosingStatus whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderClosingStatus whereGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderClosingStatus whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderClosingStatus whereSort($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderClosingStatus whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderClosingStatus whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class OrderClosingStatus extends Model
{
    protected $table = 'settings_closing_statuses';

    public function group()
    {
        return $this->belongsTo(OrderClosingStatusGroup::class, 'group_id', 'id');
    }
}
