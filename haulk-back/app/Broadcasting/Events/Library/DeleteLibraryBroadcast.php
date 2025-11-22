<?php


namespace App\Broadcasting\Events\Library;

class DeleteLibraryBroadcast extends LibraryBroadcast
{

    public const NAME = 'library.delete';

    protected function getName(): string
    {
        return self::NAME;
    }
}
