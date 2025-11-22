<?php

namespace App\Models\Support;

use App\Contracts\Alerts\AlertModel;
use App\Contracts\Roles\HasGuardUser;
use App\Events\SupportRequests\SupportRequestCreatedEvent;
use App\Events\SupportRequests\SupportRequestUpdatedEvent;
use App\Filters\SupportRequests\SupportRequestFilter;
use App\Models\Admins\Admin;
use App\Models\BaseModel;
use App\Models\Support\RequestSubjects\SupportRequestSubject;
use App\Models\Technicians\Technician;
use App\Traits\Filterable;
use App\Traits\HasFactory;
use Database\Factories\Support\SupportRequestFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int id
 * @property int|null subject_id
 * @property int technician_id
 * @property bool is_closed
 * @property Carbon|null created_at
 * @property Carbon|null updated_at
 *
 * @method static SupportRequestFactory factory(...$parameters)
 */
class SupportRequest extends BaseModel implements AlertModel
{
    use HasFactory;
    use Filterable;

    public const TABLE = 'support_requests';

    public const MORPH_NAME = 'support_request';

    protected $fillable = [
        'subject_id',
        'technician_id',
        'is_closed',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'subject_id' => 'int',
        'technician_id' => 'int',
        'is_closed' => 'bool',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    protected $dispatchesEvents = [
        'updated' => SupportRequestUpdatedEvent::class,
        'created' => SupportRequestCreatedEvent::class,
    ];

    public function technician(): BelongsTo
    {
        return $this->belongsTo(Technician::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(SupportRequestSubject::class, 'subject_id', 'id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(SupportRequestMessage::class);
    }

    public function modelFilter(): string
    {
        return SupportRequestFilter::class;
    }

    public function scopeForGuard(Builder|self $build, HasGuardUser $user): void
    {
        if (!$user instanceof Technician) {
            return;
        }

        $build->where('technician_id', $user->getId());
    }

    public function scopeJoinMessages(Builder|self $builder): void
    {
        $builder->leftJoin(
            SupportRequestMessage::TABLE,
            self::TABLE . '.id',
            '=',
            SupportRequestMessage::TABLE . '.support_request_id'
        );
    }

    public function scopeSortList(Builder|self $builder, HasGuardUser $user): void
    {
        if (!$user instanceof Admin) {
            $builder->orderByDesc('id');
            return;
        }

        $builder->orderByRaw(
            "
            is_closed ASC,
            (
                 SELECT IF(last_admin_message IS NOT NULL AND last_admin_message >= last_technician_message, 1, 0)
                 FROM (
                          SELECT MAX(CASE
                                     WHEN " . SupportRequestMessage::TABLE . ".sender_type = ?
                                         THEN " . SupportRequestMessage::TABLE . ".created_at
                                     END) AS last_technician_message,
                                 MAX(CASE
                                     WHEN " . SupportRequestMessage::TABLE . ".sender_type <> ?
                                         THEN " . SupportRequestMessage::TABLE . ".created_at
                                     END) AS last_admin_message
                          FROM " . SupportRequestMessage::TABLE . "
                          WHERE " . SupportRequestMessage::TABLE . ".support_request_id = " . SupportRequest::TABLE . ".id
                          GROUP BY " . SupportRequestMessage::TABLE . ".support_request_id
                      ) AS dates
            ) ASC,
            (
                 SELECT MAX(created_at)
                 FROM " . SupportRequestMessage::TABLE . "
                 WHERE " . SupportRequestMessage::TABLE . ".support_request_id = " . SupportRequest::TABLE . ".id
            ) ASC
        ",
            [
                Technician::MORPH_NAME,
                Technician::MORPH_NAME,
            ]
        );
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getMorphType(): string
    {
        return self::MORPH_NAME;
    }
}
