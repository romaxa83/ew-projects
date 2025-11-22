<?php

namespace App\Broadcasting\Events\News;

use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class UpdateNewsBroadcast extends NewsBroadcast implements ShouldBroadcast
{
    public const NAME = 'news.update';

    protected function getName(): string
    {
        return self::NAME;
    }
}
