<?php

namespace App\Services\Locations;

use App\Imports\States\StateImport;
use App\Models\Locations\State;
use App\Services\Excel\Excel;

class StateService
{
    public function getShortNameByState(string $stateFullName): ?string
    {
        if (mb_strlen($stateFullName) === 2) {
            return $stateFullName;
        }

        return State::query()
            ->addSelect('short_name')
            ->addName('name')
            ->where('name', $stateFullName)
            ->first()
            ?->short_name;
    }

    public function getStateByShortName(string $stateShortName): ?string
    {
        if (mb_strlen($stateShortName) > 2) {
            return $stateShortName;
        }

        return State::query()
            ->addName('name')
            ->where('short_name', $stateShortName)
            ->first()
            ?->name;
    }

    public function seed(): void
    {
        $file = database_path('files/States.xlsx');

        Excel::import(new StateImport(), $file);
    }
}
