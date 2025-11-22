<?php

namespace App\Http\Resources\Parsers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

/**
 * Class ParsedPdfResource
 *
 * @package App\Http\Resources\Parsers
 */
class ParsedPdfResource extends JsonResource
{
    /**
     * @param Request $request
     * @return array
     */
    public function toArray($request)
    {
        if (is_iterable($this->resource)) {
            $result = [];

            foreach ($this->resource as $key => $value) {
                $result[$key] = $this->resolveValue($value);
            }

            return $result;
        }

        return $this->resource;
    }

    /**
     * @param string|Collection $value
     * @return AnonymousResourceCollection|string
     */
    protected function resolveValue($value)
    {
        if (is_iterable($value)) {
            return self::collection($value);
        }

        return $value;
    }
}
