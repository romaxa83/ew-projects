<?php

namespace App\Models\History;

use App\Models\BaseModel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property int $row_id
 * @property string|null $address
 * @property string|null $email
 * @property string|null $name
 * @property string|null $certificate
 * @property string|null $phone
 * @property string|null $etc
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class OrderOwner extends BaseModel
{
    use HasFactory;

    public const TABLE = 'history_order_owners';
    protected $table = self::TABLE;
}
