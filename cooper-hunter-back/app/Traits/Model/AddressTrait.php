<?php

namespace App\Traits\Model;

use Illuminate\Database\Eloquent\Builder;

/**
 * @see AddressTrait::getFullAddressAttribute()
 * @property-read string full_address
 */
trait AddressTrait
{
    // example - 7 Levis Circle Str. 328571 , Los Angeles, California, 19804
    public function getFullAddressAttribute(): string
    {
        $address = $this->address_line_1;
        if($this->address_line_2) {
            $address .= ', ' . $this->address_line_2;
        }
        $address .= ', ' . $this->city . ', ' . $this->state->short_name . ', ' . $this->zip;

        return  $address;
    }
}

