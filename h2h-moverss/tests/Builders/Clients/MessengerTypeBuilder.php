<?php

namespace Tests\Builders\Clients;

use App\Models\Client;
use Tests\Builders\BaseBuilder;

class MessengerTypeBuilder extends BaseBuilder
{
    function modelClass(): string
    {
        return Client\MessengerType::class;
    }
}
