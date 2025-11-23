<?php

namespace App\Models\Order;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Order\CustomerPage
 *
 * @property int $id
 * @property int $division_id
 * @property string $name
 * @property string $title
 * @property string $text
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerPage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerPage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerPage query()
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerPage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerPage whereDivisionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerPage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerPage whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerPage whereText($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerPage whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerPage whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class CustomerPage extends Model
{
    protected $table = 'customer_pages';
    protected $fillable = ['title', 'text'];
}
