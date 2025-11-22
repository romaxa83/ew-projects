<?php

namespace App\Services\ImportLocations\ItemParts;

use App\Services\ImportLocations\Abstractions\AbstractItem;

class City extends AbstractItem
{
    protected $synonyms = [
        'city' => 'name',
        'state_name' => 'state_id',
        'zip' => 'zip',
        'timezone' => 'timezone',
    ];
}
