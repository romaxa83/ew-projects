<?php

namespace App\Models\Settings;

use Illuminate\Database\Eloquent\Model;


/**
 * App\Models\Settings\OrderClosingStatusGroup
 *
 * @property int $id
 * @property string $title
 * @property int $sort
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property string|null $deleted_at
 * @method static \Illuminate\Database\Eloquent\Builder|OrderClosingStatusGroup newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OrderClosingStatusGroup newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OrderClosingStatusGroup query()
 * @method static \Illuminate\Database\Eloquent\Builder|OrderClosingStatusGroup whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderClosingStatusGroup whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderClosingStatusGroup whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderClosingStatusGroup whereSort($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderClosingStatusGroup whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderClosingStatusGroup whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class OrderClosingStatusGroup extends Model
{
    protected $table = 'settings_closing_statuses_groups';
}
