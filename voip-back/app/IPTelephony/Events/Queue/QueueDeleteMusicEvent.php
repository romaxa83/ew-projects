<?php

namespace App\IPTelephony\Events\Queue;

use App\Models\Musics\Music;

class QueueDeleteMusicEvent
{
    public function __construct(
        protected Music $model
    )
    {}

    public function getModel(): Music
    {
        return $this->model;
    }
}

