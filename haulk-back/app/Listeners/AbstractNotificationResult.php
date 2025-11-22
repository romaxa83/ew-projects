<?php

namespace App\Listeners;

abstract class AbstractNotificationResult
{
    protected function toEmailString($data): string
    {
        if (!isset($data['mail'])) {
            return '';
        }

        if (is_array($data['mail'])) {
            return implode(', ', $data['mail']);
        }

        if (is_string($data['mail'])) {
            return $data['mail'];
        }

        return '';
    }
}
