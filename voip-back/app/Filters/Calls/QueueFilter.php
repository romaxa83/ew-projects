<?php

namespace App\Filters\Calls;

use App\Models\Calls\Queue;
use App\Traits\Filter\IdFilterTrait;
use App\Traits\Filter\StatusFilterTrait;
use EloquentFilter\ModelFilter;

/**
 * @mixin Queue
 */
class QueueFilter extends ModelFilter
{
    use IdFilterTrait;
    use StatusFilterTrait;

    public function department(string|int $value): void
    {
        $this->where('department_id', $value);
    }

    public function serialNumber(string $value): void
    {
        $this->where('serial_number', 'like', '%'.$value.'%');
    }

    public function case(string $value): void
    {
        $this->where('case_id', 'like', '%'.$value.'%');
    }

    public function search(string $value): void
    {
        $this->whereRaw("(caller_num LIKE '%{$value}%' OR caller_name LIKE '%{$value}%' OR comment LIKE '%{$value}%')")
//            ->orWhereRaw("caller_name LIKE '%{$value}%'")
//            ->orWhereRaw("comment LIKE '%{$value}%'")
        ;
    }
}
