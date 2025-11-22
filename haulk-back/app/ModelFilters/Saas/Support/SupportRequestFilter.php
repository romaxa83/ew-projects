<?php

namespace App\ModelFilters\Saas\Support;

use Illuminate\Support\Carbon;
use EloquentFilter\ModelFilter;

class SupportRequestFilter extends ModelFilter
{
    /**
    * Related Models that have ModelFilters as well as the method on the ModelFilter
    * As [relationMethod => [input_key1, input_key2]].
    *
    * @var array
    */
    public $relations = [];

    public function onlyUser(int $userId): SupportRequestFilter
    {
        return $this->where('user_id', $userId);
    }

    public function status(int $status): SupportRequestFilter
    {
        return $this->where('status', $status);
    }

    public function label(int $label): SupportRequestFilter
    {
        return $this->where('label', $label);
    }

    public function source(int $source): SupportRequestFilter
    {
        return $this->where('source', $source);
    }

    public function admin(int $adminId): SupportRequestFilter
    {
        return $this->where('admin_id', $adminId);
    }

    public function dateFrom(string $date_from): SupportRequestFilter
    {
        return $this->where('created_at', '>=', Carbon::make($date_from)->startOfDay());
    }

    public function dateTo(string $date_to): SupportRequestFilter
    {
        return $this->where('created_at', '<=', Carbon::make($date_to)->endOfDay());
    }
}
