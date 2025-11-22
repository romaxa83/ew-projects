<?php

namespace App\ModelFilters\Logs;

use App\Models\Logs\Log;
use EloquentFilter\ModelFilter;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @mixin Log
 */
class LogFilter extends ModelFilter
{

    public function levelNames(array $names): void
    {
        if (!empty($names)) {
            $this->whereIn('level_name', $names);
        }
    }

    public function message(string $message): void
    {
        $message = Str::lower($message);

        $this->whereRaw('LOWER(message) LIKE ?', ["%$message%"]);
    }

    public function dateFrom(string $date): void
    {
        $unixTime = Carbon::createFromFormat(config('formats.datetime'), $date);

        $this->where('unix_time', '>=', $unixTime->getTimestamp());
    }

    public function dateTo(string $date): void
    {
        $unixTime = Carbon::createFromFormat(config('formats.datetime'), $date);

        $this->where('unix_time', '<=', $unixTime->getTimestamp());
    }

}
