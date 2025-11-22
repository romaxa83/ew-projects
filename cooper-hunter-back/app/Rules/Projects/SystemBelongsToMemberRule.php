<?php

namespace App\Rules\Projects;

use App\Contracts\Members\Member;
use App\Models\Projects\System;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Database\Eloquent\Builder;

class SystemBelongsToMemberRule implements Rule
{
    public function __construct(protected Member $member)
    {
    }

    public function passes($attribute, $value): bool
    {
        return System::query()
            ->whereHas(
                'project',
                fn(Builder $b) => $b
                    ->where('member_type', $this->member->getMorphType())
                    ->where('member_id', $this->member->getId())
            )
            ->exists();
    }

    public function message(): string
    {
        return __('validation.exists', ['attribute' => 'System']);
    }
}
