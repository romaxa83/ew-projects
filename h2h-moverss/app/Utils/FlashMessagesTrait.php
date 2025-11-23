<?php

namespace App\Utils;

trait FlashMessagesTrait
{
    /**
     * Push notify to user session.
     * @param $message
     * @param  string  $level  Default: warning. Options: success, info, warning, danger
     * @return array
     */
    protected static function message($message, string $level = 'warning'): array
    {
        if (session()->has('messages')) {
            $messages = session()->pull('messages');
        }

        if ($level === 'error') {
            $level = 'danger';
        }

        $messages[] = $message = [
            'level' => $level,
            'message' => $message
        ];

        session()->flash('messages', $messages);

        return $message;
    }
}
