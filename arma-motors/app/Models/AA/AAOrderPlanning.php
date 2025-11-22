<?php

namespace App\Models\AA;

use App\Models\BaseModel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * составная часть данных AAOrder, в данных это секция planning,
 * по этим данным и происходит просчет времени
 *
 * @property int $id
 * @property int $aa_order_id
 * @property Carbon $start_date
 * @property Carbon $end_date
 * @property string $post_uuid  // в данных это [workshop]
 *
 */
class AAOrderPlanning extends BaseModel
{
    use HasFactory;

    public $timestamps = false;

    public const TABLE = 'aa_order_planning';
    protected $table = self::TABLE;

    protected $dates = [
        'start_date',
        'end_date',
    ];
}
