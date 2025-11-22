<?php

namespace App\Exports;

use Carbon\CarbonImmutable;

class BaseExport
{
    protected function formatDate(?int $timestamp, ?string $timezone = null): string
    {
        if (is_null($timestamp)) {
            return '';
        }

        $date = CarbonImmutable::createFromTimestamp($timestamp);

        if ($timezone) {
            $date->setTimezone($timezone);
        }

        return $date->format('m/d/Y g:i A T');
    }

    protected function formatDateForTracking(?int $timestamp, ?string $timezone = null): string
    {
        if (is_null($timestamp)) {
            return '';
        }

        $date = CarbonImmutable::createFromTimestamp($timestamp, $timezone);

        return $date->format('m/d/Y g:i A T');
    }
}
