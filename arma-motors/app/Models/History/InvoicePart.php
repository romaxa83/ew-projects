<?php

namespace App\Models\History;

use App\Models\BaseModel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property int $row_id
 * @property string|null $name
 * @property string|null $ref
 * @property string|null $unit
 * @property float|null $discounted_price
 * @property float|null $price
 * @property float|null $quantity
 * @property float|null $rate
 * @property float|null $sum
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class InvoicePart extends BaseModel
{
    use HasFactory;

    public const TABLE = 'history_invoice_parts';
    protected $table = self::TABLE;
}
