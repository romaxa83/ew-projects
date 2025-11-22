<?php

namespace App\Broadcasting\Events\News;

use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class CreateNewsBroadcast extends NewsBroadcast implements ShouldBroadcast
{
    public const NAME = 'news.create';

    protected function getName(): string
    {
        return self::NAME;
    }
}
