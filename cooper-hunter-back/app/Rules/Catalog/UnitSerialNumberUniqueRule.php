<?php

namespace App\Rules\Catalog;

use App\Contracts\Members\Member;
use App\Models\Projects\Pivot\SystemUnitPivot;
use App\Models\Projects\Project;
use App\Models\Projects\System;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Database\Eloquent\Builder;

class UnitSerialNumberUniqueRule implements Rule
{
    protected ?int $ignoreSystem = null;
    protected ?Member $member = null;

    public function ignoreSystem(?int $ignore): self
    {
        $this->ignoreSystem = $ignore;

        return $this;
    }

    /**
     * Ensure, that given member use serial number only once
     */
    public function uniqueForMember(Member $member): self
    {
        $this->member = $member;

        return $this;
    }

    public function passes($attribute, $value): bool
    {
        $query = SystemUnitPivot::query()
            ->when($this->ignoreSystem, fn(Builder $b) => $b->where('system_id', '<>', $this->ignoreSystem))
            ->where('product_id', $value['product_id'])
            ->where('serial_number', $value['serial_number']);

        if ($this->member) {
            $query->whereHas(
                'system',
                fn(Builder|System $s) => $s
                    ->whereHas(
                        'project',
                        fn(Builder|Project $p) => $p
                            ->where('member_id', $this->member->getKey())
                            ->where('member_type', $this->member->getMorphType())
                    )
            );
        }

        return $query->doesntExist();
    }

    public function message(): string
    {
        return __('validation.custom.unit-serial-number-used');
    }
}
