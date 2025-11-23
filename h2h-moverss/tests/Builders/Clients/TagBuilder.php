<?php

namespace Tests\Builders\Clients;

use App\Models\Client;
use Tests\Builders\BaseBuilder;

class TagBuilder extends BaseBuilder
{
    function modelClass(): string
    {
        return Client\Tag::class;
    }
}
