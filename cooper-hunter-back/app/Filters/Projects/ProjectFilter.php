<?php

namespace App\Filters\Projects;

use App\Filters\BaseModelFilter;
use App\Models\Projects\Project;
use App\Models\Technicians\Technician;
use App\Models\Users\User;
use App\Traits\Filter\BetweenDateRangeFilterTrait;
use App\Traits\Filter\IdFilterTrait;
use App\Traits\Filter\LikeRawFilterTrait;
use Illuminate\Database\Eloquent\Builder;

/**
 * @mixin Project
 */
class ProjectFilter extends BaseModelFilter
{
    use IdFilterTrait;
    use LikeRawFilterTrait;
    use BetweenDateRangeFilterTrait;

    public function user(int $user): void
    {
        $this->belongsToUser($user);
    }

    public function technician(int $technician): void
    {
        $this->belongsToTechnician($technician);
    }

    public function memberType(string $memberType): void
    {
        $this->where('member_type', $memberType);
    }

    public function name(string $name): void
    {
        $this->likeRaw('name', $name);
    }

    public function memberName(string $memberName): void
    {
        $this->whereHasMorph(
            'member',
            [
                Technician::class,
                User::class,
            ],
            fn(Builder $builder) => $this->likeRaw("concat(first_name, ' ', last_name)", $memberName, $builder)
        );
    }

    public function memberEmail(string $memberEmail): void
    {
        $memberEmail = '%' . mb_convert_case($memberEmail, MB_CASE_LOWER) . '%';

        $this->whereHasMorph(
            'member',
            [
                Technician::class,
                User::class,
            ],
            fn(Builder $builder) => $builder->whereRaw(
                "lower(email) LIKE ?",
                [$memberEmail]
            )
        );
    }

    public function member(int $memberId): void
    {
        $this->where('member_id', $memberId);
    }

    public function serialNumber(string $serialNumber): void
    {
        $this->whereHas(
            'systems',
            fn(Builder $systems) => $systems->whereHas(
                'units',
                fn(Builder $units) => $units->where('serial_number', $serialNumber)
            )
        );
    }
}
