<?php


namespace App\ModelFilters\Payrolls;


use EloquentFilter\ModelFilter;
use Illuminate\Database\Eloquent\Builder;

class PayrollFilter extends ModelFilter
{
    public function load(string $load_id)
    {
        return $this->whereHas('orders', function ($q) use ($load_id) {
            $q->whereRaw('lower(load_id) like ?', ['%' . escapeLike(mb_convert_case($load_id, MB_CASE_LOWER)) . '%']);
        });
    }

    public function driver(string $driver_id)
    {
        return $this->where('driver_id', (int) $driver_id);
    }

    public function role($value)
    {
        return $this->whereHas('driver', function($q) use ($value) {
            $q->whereHas('roles', function (Builder $builder) use ($value) {
                $builder->where('id', $value);
            });
        });
    }

    public function dateFrom(string $date_from)
    {
        return $this->where('created_at', '>=', $date_from . ' 00:00:00');
    }

    public function dateTo(string $date_to)
    {
        return $this->where('created_at', '<=', $date_to . ' 23:59:59');
    }

    public function notPaid()
    {
        $request = request();

        if ($request && $request->has('not_paid') && $request->boolean('not_paid')) {
            return $this->where('is_paid', false);
        }
    }
}
