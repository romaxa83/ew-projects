<?php

namespace App\Models\History;

use App\Models\BaseModel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property int $row_id
 * @property string|null $fio
 * @property Carbon|null $date
 * @property string|null $name
 * @property string|null $number
 * @property string|null $position
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class OrderDispatcher extends BaseModel
{
    use HasFactory;

    public const TABLE = 'history_order_dispatchers';
    protected $table = self::TABLE;

    protected $dates = [
        'date',
    ];
}
