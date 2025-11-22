<?php

namespace App\Models\Commercial;

use App\Casts\EmailCast;
use App\Casts\PhoneCast;
use App\Contracts\Members\HasCommercialProjects;
use App\Enums\Commercial\CommercialCredentialsStatusEnum;
use App\Enums\Formats\DatetimeEnum;
use App\Filters\Commercial\CredentialsRequestFilter;
use App\Models\BaseModel;
use App\Models\Technicians\Technician;
use App\Traits\Filterable;
use App\Traits\HasFactory;
use Database\Factories\Commercial\CredentialsRequestFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @see CredentialsRequest::scopeHasValidPendingRequest()
 * @method Builder hasValidPendingRequest()
 *
 * @method static CredentialsRequestFactory factory(...$parameters)
 *
 * @property-read HasCommercialProjects member
 * @property-read RDPAccount rdpAccount
 */
class CredentialsRequest extends BaseModel
{
    use HasFactory;
    use Filterable;

    public const TABLE = 'credentials_requests';

    public const ALLOWED_SORTING_FIELDS = [
        'id',
        'status',
        'created_at',
        'processed_at',
        'end_date',
    ];

    protected $table = self::TABLE;

    protected $casts = [
        'company_phone' => PhoneCast::class,
        'company_email' => EmailCast::class,
        'status' => CommercialCredentialsStatusEnum::class,
        'processed_at' => DatetimeEnum::DEFAULT,
        'end_date' => DatetimeEnum::DEFAULT,
    ];

    public function modelFilter(): string
    {
        return CredentialsRequestFilter::class;
    }

    public function commercialProject(): BelongsTo|CommercialProject
    {
        return $this->belongsTo(CommercialProject::class);
    }

    public function member(): MorphTo|HasCommercialProjects
    {
        return $this->morphTo('member');
    }

    public function rdpAccount()
    {
        return $this->hasOneThrough(
            RDPAccount::class,
            Technician::class,
            'id',
            'member_id',
            'member_id',
            'id',

        );
    }

    public function scopeHasValidPendingRequest(Builder|self $b): void
    {
        $b->where('status', CommercialCredentialsStatusEnum::NEW);
    }
}
