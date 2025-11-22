<?php

namespace App\Models\History;

use App\Models\BaseModel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property int $row_id
 * @property string|null $name
 * @property string|null $date
 * @property string|null $number
 * @property string|null $contract
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class OrderPayer extends BaseModel
{
    use HasFactory;

    public const TABLE = 'history_order_payers';
    protected $table = self::TABLE;

    protected $dates = [
        'date',
    ];
}
