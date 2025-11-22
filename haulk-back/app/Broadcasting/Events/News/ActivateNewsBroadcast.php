<?php

namespace App\Broadcasting\Events\News;

use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class ActivateNewsBroadcast extends NewsBroadcast implements ShouldBroadcast
{
    public const NAME = 'news.activate';

    protected function getName(): string
    {
        return self::NAME;
    }
}
