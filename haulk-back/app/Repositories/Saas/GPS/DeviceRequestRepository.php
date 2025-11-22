<?php

namespace App\Repositories\Saas\GPS;

use App\Models\Saas\GPS\DeviceRequest;
use DomainException;
use Illuminate\Http\Response;

class DeviceRequestRepository
{
    public function getBy(
        $field,
        $value,
        array $relations = [],
        $withException = false,
        $exceptionMessage = 'Model not found'
    ): ?DeviceRequest
    {
        $result = DeviceRequest::query()
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


