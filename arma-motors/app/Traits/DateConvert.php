<?php

namespace App\Traits;

use Carbon\Carbon;

trait DateConvert
{
    public function fromAAToTimestamp(string|null $date): int|null
    {
        if(!$date){
            return null;
        }
        return Carbon::createFromFormat('d.m.Y', $date)->timestamp;
    }
}

