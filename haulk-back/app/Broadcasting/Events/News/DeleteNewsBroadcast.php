<?php

namespace App\Broadcasting\Events\News;

use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class DeleteNewsBroadcast extends NewsBroadcast implements ShouldBroadcast
{
    public const NAME = 'news.delete';

    protected function getName(): string
    {
        return self::NAME;
    }
}
