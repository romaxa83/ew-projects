<?php

namespace App\Models\Commercial;

use App\Casts\EncryptValueCast;
use App\Contracts\Members\HasCommercialProjects;
use App\Filters\Commercial\RDPAccountFilter;
use App\Models\BaseModel;
use App\Traits\Filterable;
use App\Traits\HasFactory;
use Database\Factories\Commercial\RDPAccountFactory;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;

/**
 * @method static RDPAccountFactory factory(...$parameters)
 */
class RDPAccount extends BaseModel
{
    use HasFactory;
    use Filterable;

    public const TABLE = 'rdp_accounts';

    public const ALLOWED_SORTING_FIELDS = [
        'start_date',
        'end_date',
        'login',
    ];

    public $timestamps = false;

    protected $table = self::TABLE;

    protected $casts = [
        'password' => EncryptValueCast::class,
        'active' => 'boolean',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    public function isValid(): bool
    {
        return $this->active && $this->end_date > now();
    }

    public function modelFilter(): string
    {
        return RDPAccountFilter::class;
    }

    public function member(): MorphTo|HasCommercialProjects
    {
        return $this->morphTo('member');
    }

    public function getLoginString(): string
    {
        if ($login = $this->login) {
            return $login;
        }

        return Str::slug(Str::before($this->member->getEmailString(), '@'), '');
    }

    public function getPasswordMaxAge(): int
    {
        return dateIntervalToSeconds($this->end_date->diffAsCarbonInterval());
    }
}
