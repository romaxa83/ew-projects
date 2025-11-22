<?php

namespace App\Models\Support\Supports;

use App\Casts\PhoneCast;
use App\Models\BaseHasTranslation;
use App\Traits\HasFactory;
use Database\Factories\Support\Supports\SupportFactory;

/**
 * @property int id
 * @property string phone
 *
 * @method static SupportFactory factory(...$parameters)
 */
class Support extends BaseHasTranslation
{
    use HasFactory;

    public const TABLE = 'supports';

    public $timestamps = false;

    protected $table = self::TABLE;

    protected $casts = [
        'phone' => PhoneCast::class,
    ];
}
