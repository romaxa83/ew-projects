<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Settings
 *
 * @property int $id
 * @property string $name
 * @property int|null $division_id
 * @property string|null $value
 * @property mixed|null $miscs
 * @property string $description
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Settings newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Settings newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Settings query()
 * @method static \Illuminate\Database\Eloquent\Builder|Settings whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Settings whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Settings whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Settings whereMiscs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Settings whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Settings whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Settings whereValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Settings whereDivisionId($value)
 * @mixin \Eloquent
 */
class Settings extends Model
{
    protected $table = 'settings';
    protected $casts = [
        'miscs' => 'json',
    ];
}
