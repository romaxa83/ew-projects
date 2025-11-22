<?php


namespace App\Services\ImportLocations\ItemParts;


use App\Services\ImportLocations\Abstractions\AbstractItem;

class State extends AbstractItem
{
    protected $synonyms = [
        'state_name' => 'name',
        'state_id' => 'state_short_name'
    ];
}