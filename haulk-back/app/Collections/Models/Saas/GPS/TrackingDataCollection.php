<?php

namespace App\Collections\Models\Saas\GPS;

use Illuminate\Database\Eloquent\Collection;

class TrackingDataCollection extends Collection
{
    public function sortByDriverName(string $type): self
    {
        $sorted = $this->sortBy([
            ['driverName', $type],
        ]);

        $tmp = [];

        $sorted = $sorted->filter(function($value) use (&$tmp) {
            if($value->driverName == null){
                $tmp[] = $value;
            }
            return $value->driverName != null;
        });

        return $sorted->push(...$tmp);
    }
}


