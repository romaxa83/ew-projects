<?php

namespace App\Broadcasting\Events\Contact;

use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class CreateContactBroadcast extends ContactBroadcast implements ShouldBroadcast
{
    public const NAME = 'contact.create';

    protected function getName(): string
    {
        return self::NAME;
    }
}
