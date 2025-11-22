<?php

namespace App\Broadcasting\Events\Contact;

use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class DeleteContactBroadcast extends ContactBroadcast implements ShouldBroadcast
{
    public const NAME = 'contact.delete';

    protected function getName(): string
    {
        return self::NAME;
    }
}
