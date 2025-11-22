<?php

namespace App\Rules\Catalog;

use App\Contracts\Members\Member;
use App\Models\Projects\Pivot\SystemUnitPivot;
use App\Models\Projects\Project;
use App\Models\Projects\System;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Validator;
use Rebing\GraphQL\Error\ValidationError;

class SystemUnitSerialNumberUniqueRule implements Rule
{
    protected ?Member $member = null;

    /**
     * Ensure, that given member use serial number only once
     */
    public function uniqueForMember(Member $member): self
    {
        $this->member = $member;

        return $this;
    }

    /**
     * @throws ValidationError
     */
    public function passes($attribute, $value): bool
    {
        foreach ($value as $systemKey => $system) {
            $id = $system['id'] ?? false;

            foreach ($system['units'] ?? [] as $unitKey => $unit) {
                $query = SystemUnitPivot::query()
                    ->when($id, fn(Builder $b) => $b->where('system_id', '<>', $id))
                    ->where('product_id', $unit['product_id'])
                    ->where('serial_number', $unit['serial_number']);

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

                if ($query->exists()) {
                    $attribute .= ".$systemKey.units.$unitKey.serial_number";

                    $validator = Validator::make([], []);
                    $validator->after(fn($v) => $v->errors()->add($attribute, $this->message()));

                    throw new ValidationError('validation', $validator);
                }
            }
        }

        return true;
    }

    public function message(): string
    {
        return __('validation.custom.unit-serial-number-used');
    }
}
