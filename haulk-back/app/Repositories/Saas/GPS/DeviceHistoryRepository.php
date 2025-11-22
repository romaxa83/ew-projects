<?php

namespace App\Repositories\Saas\GPS;

use App\Models\Saas\GPS\DeviceHistory;
use DomainException;
use Illuminate\Http\Response;

class DeviceHistoryRepository
{
    public function getBy(
        $field,
        $value,
        array $relations = [],
        $withException = false,
        $exceptionMessage = 'Model not found'
    ): ?DeviceHistory
    {
        $result = DeviceHistory::query()
            ->with($relations)
            ->where($field, $value)
            ->first()
        ;

        if ($withException && null === $result) {
            throw new DomainException($exceptionMessage, Response::HTTP_NOT_FOUND);
        }

        return $result;
    }
}


