<?php

namespace App\Repositories\Musics;

use App\Models\Musics\Music;
use App\Repositories\AbstractRepository;

final class MusicRepository extends AbstractRepository
{
    public function modelClass(): string
    {
        return Music::class;
    }
}
