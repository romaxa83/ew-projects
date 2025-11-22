<?php

namespace App\Filters\SupportRequests;

use App\Filters\BaseModelFilter;
use App\Models\Support\SupportRequest;
use App\Traits\Filter\IdFilterTrait;
use App\Traits\Filter\LikeRawFilterTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class SupportRequestFilter extends BaseModelFilter
{
    use IdFilterTrait;
    use LikeRawFilterTrait;

    public const TABLE = SupportRequest::TABLE;

    public function technicianEmail(string $email): void
    {
        $this->whereHas(
            'technician',
            fn(Builder $builder) => $this->likeRaw('email', $email, $builder)
        );
    }

    public function technicianName(string $name): void
    {
        $this->whereHas(
            'technician',
            fn(Builder $builder) => $this->likeRaw("concat(first_name, ' ', last_name)", $name, $builder)
        );
    }

    public function dateFrom(string $date): void
    {
        $this->where(
            'created_at',
            '>=',
            Carbon::parse($date)
                ->startOfDay()
                ->toDateTimeString()
        );
    }

    public function dateTo(string $date): void
    {
        $this->where(
            'created_at',
            '<=',
            Carbon::parse($date)
                ->endOfDay()
                ->toDateTimeString()
        );
    }

    public function subject(int $subjectId): void
    {
        $this->where('subject_id', $subjectId);
    }

    public function closed(bool $closed): void
    {
        $this->where('is_closed', $closed);
    }
}
