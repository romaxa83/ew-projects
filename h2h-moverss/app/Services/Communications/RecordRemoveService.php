<?php

namespace App\Services\Communications;

use App\Models\Communications\CommunicationRecord;
use Illuminate\Database\Eloquent\Model;

final class RecordRemoveService
{
    public static function handler(Model $model): bool
    {
        return  CommunicationRecord::query()
            ->where('entity_type', $model::MORPH_NAME)
            ->where('entity_id', $model->id)
            ->delete()
        ;
    }
}
