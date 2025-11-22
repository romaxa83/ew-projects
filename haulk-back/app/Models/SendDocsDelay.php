<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SendDocsDelay extends Model
{
    use HasFactory;

    protected $fillable = [
        'sender_id',
        'order_id',
        'inspection_type',
        'request_data',
    ];

    protected $casts = [
        'request_data' => 'array',
    ];
}
