<?php


namespace App\Broadcasting\Events\Library;

class CreateLibraryBroadcast extends LibraryBroadcast
{

    public const NAME = 'library.create';

    protected function getName(): string
    {
        return self::NAME;
    }
}
