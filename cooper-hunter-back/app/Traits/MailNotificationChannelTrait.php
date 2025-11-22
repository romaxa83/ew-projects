<?php

namespace App\Traits;

/**
 * @method static static|Factory factory()
 */
trait MailNotificationChannelTrait
{
    /**
     * @return string[]
     */
    public function viaQueues(): array
    {
        return [
            'mail' => 'mail-notification'
        ];
    }
}
