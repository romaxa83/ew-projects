<?php

namespace App\Models\Support\RequestSubjects;

use App\Filters\SupportRequests\SupportRequestSubjectFilter;
use App\Models\BaseHasTranslation;
use App\Models\Support\SupportRequest;
use App\Traits\Filterable;
use App\Traits\HasFactory;
use App\Traits\Model\ActiveForGuardScopeTrait;
use App\Traits\Model\ActiveScopeTrait;
use App\Traits\Model\SetSortAfterCreate;
use Database\Factories\Support\RequestSubjects\SupportRequestSubjectFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int id
 * @property int sort
 * @property bool active
 * @property Carbon|null created_at
 * @property Carbon|null updated_at
 *
 * @method static SupportRequestSubjectFactory factory(...$parameters)
 */
class SupportRequestSubject extends BaseHasTranslation
{
    use HasFactory;
    use SetSortAfterCreate;
    use ActiveScopeTrait;
    use ActiveForGuardScopeTrait;
    use Filterable;

    public const TABLE = 'support_request_subjects';

    protected $fillable = [
        'sort',
        'active',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'sort' => 'int',
        'active' => 'bool',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function modelFilter(): string
    {
        return SupportRequestSubjectFilter::class;
    }

    public function supportRequests(): HasMany
    {
        return $this->hasMany(SupportRequest::class, 'subject_id', 'id');
    }
}
