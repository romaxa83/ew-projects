<?php

namespace App\Models\Admin;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string|null $ip_address
 * @property int $admin_id
 * @property Carbon $created_at
 */

class Login extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'admin_logins';

    protected $casts = [
        'created_at' => 'datetime',
    ];

    protected $fillable = [
        'admin_id',
        'created_at',
        'ip_address',
    ];

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }
}
