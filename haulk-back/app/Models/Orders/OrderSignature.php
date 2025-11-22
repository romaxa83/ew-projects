<?php

namespace App\Models\Orders;

use App\Models\Users\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class OrderSignature
 * @package App\Models\Orders
 *
 * @see OrderSignature::sender()
 * @property User sender
 *
 * @see OrderSignature::order()
 * @property Order order
 */
class OrderSignature extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'order_id',
        'user_id',
        'email',
        'last_name',
        'first_name',
        'inspection_location',
        'signature_token',
        'signed',
        'signed_time',
        'updated_at',
        'created_at'
    ];

    protected $casts = [
        'signed' => 'boolean',
        'signed_time' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
        'created_at' => 'datetime:Y-m-d H:i:s'
    ];


    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
