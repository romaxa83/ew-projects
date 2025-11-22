<?php

namespace App\Services\ImportLocations\Worker;

use App\Models\Locations\City;
use App\Models\Locations\State;

class DatabaseWriter
{
    /**
     * @var Import
     */
    private $import;

    /**
     * DatabaseWriter constructor.
     * @param Import $import
     */
    public function __construct(Import $import)
    {
        $this->import = $import;
        $this->run();
    }

    private function run()
    {
        $this->statesWrite();
    }

    public function statesWrite()
    {
        $values = [];
        foreach ($this->import->states as $state) {
            $values[] = [
                'id' => $state->id,
                'name' => $state->name,
                'status' => 1,
                'state_short_name' => $state->state_short_name,
            ];
        }
        \DB::table(State::TABLE_NAME)->insertOrIgnore($values);
        $this->citiesWriter();
    }

    public function citiesWriter()
    {
        $values = [];
        foreach ($this->import->cities as $cityItem) {
            $values[] = [
                'id' => $cityItem->id,
                'name' => $cityItem->name,
                'state_id' => $cityItem->state_id,
                'zip' => $cityItem->zip,
                'timezone' => $cityItem->timezone,
                'status' => 1,
            ];
        }
        foreach (array_chunk($values, 5000) as $t) {
            \DB::table(City::TABLE_NAME)->insertOrIgnore($t);
        }
    }
}
