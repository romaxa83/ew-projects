<?php

namespace App\Broadcasting\Events\News;

use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class DeactivateNewsBroadcast extends NewsBroadcast implements ShouldBroadcast
{
    public const NAME = 'news.deactivate';

    protected function getName(): string
    {
        return self::NAME;
    }
}
