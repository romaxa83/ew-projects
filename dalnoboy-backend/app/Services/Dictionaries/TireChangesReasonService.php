<?php

namespace App\Services\Dictionaries;

use App\Models\Dictionaries\TireChangesReason;
use Illuminate\Database\Eloquent\Collection;

class TireChangesReasonService
{
    public function show(array $relation, array $select): Collection
    {
        return TireChangesReason::select($select)
            ->with($relation)
            ->orderBy('id', 'desc')
            ->get();
    }
}
