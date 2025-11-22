<?php

namespace App\Broadcasting\Events\Contact;

use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class UpdateContactBroadcast extends ContactBroadcast implements ShouldBroadcast
{
    public const NAME = 'contact.update';

    protected function getName(): string
    {
        return self::NAME;
    }
}
