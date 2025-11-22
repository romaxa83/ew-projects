<?php

namespace App\Filters\Orders\Dealer;

use App\Traits\Filter\IdFilterTrait;
use EloquentFilter\ModelFilter;
use Illuminate\Support\Carbon;

class OrderFilter extends ModelFilter
{
    use IdFilterTrait;

    public function status($value): void
    {
        $this->where('status', $value);
    }

    public function statuses(array $value): void
    {
        $this->whereIn('status', $value);
    }

    public function dealer(array|int $value): self
    {
        if (is_array($value)) {
            return $this->whereIn('dealer_id', $value);
        }
        return $this->where('dealer_id', $value);
    }

    public function shippingAddress(array|int $value): self
    {
        if (is_array($value)) {
            return $this->orWhereIn('shipping_address_id', $value);
        }
        return $this->orWhere('shipping_address_id', $value);

//        if(is_array($value)){
//            return $this->whereIn('shipping_address_id', $value);
//        }
//        return  $this->where('shipping_address_id', $value);
    }

    public function po(string $value): self
    {
        return $this->where('po', 'like', $value . "%");
    }

    public function company($value): self
    {
        return $this->whereHas('dealer', function ($b) use ($value) {
            $b->where('company_id', $value);
        });
    }

    public function location($value): self
    {
        return $this->where('shipping_address_id', $value);
    }

    public function dateFrom(string $date): void
    {
//        $date = Carbon::createFromFormat('d/m/Y', $date)
        $date = Carbon::parse($date)
            ->startOfDay()
            ->toDateTimeString();

        $this->where('approved_at', '>=', $date);
    }

    public function dateTo(string $date): void
    {
        $date = Carbon::parse($date)
            ->endOfDay()
            ->toDateTimeString();

        $this->where('approved_at', '<=', $date);
    }
}
