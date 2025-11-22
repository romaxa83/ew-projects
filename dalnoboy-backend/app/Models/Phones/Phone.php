<?php

namespace App\Models\Phones;

use App\Casts\PhoneCast;
use App\Models\BaseModel;
use App\Traits\HasFactory;
use Database\Factories\Phones\PhoneFactory;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @method static PhoneFactory factory()
 */
class Phone extends BaseModel
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'owner_type',
        'owner_id',
        'phone',
        'is_default'
    ];

    protected $casts = [
        'phone' => PhoneCast::class,
        'is_default' => 'boolean'
    ];

    public function owner(): MorphTo
    {
        return $this->morphTo();
    }
}
