<?php

namespace App\Models\History;

use App\Models\BaseModel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property int $row_id
 * @property string|null $name
 * @property float|null $amount_including_vat
 * @property float|null $amount_without_vat
 * @property float|null $coefficient
 * @property float|null $price
 * @property float|null $price_with_vat
 * @property float|null $price_without_vat
 * @property string|null $ref
 * @property float|null $rate
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class OrderJob extends BaseModel
{
    use HasFactory;

    public const TABLE = 'history_order_jobs';
    protected $table = self::TABLE;
}
