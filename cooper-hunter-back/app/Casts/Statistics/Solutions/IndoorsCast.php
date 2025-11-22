<?php

namespace App\Casts\Statistics\Solutions;

use App\Collections\Statistics\Solutions\IndoorsCollection;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use JsonException;

class IndoorsCast implements CastsAttributes
{
    /**
     * @throws JsonException
     */
    public function get($model, string $key, $value, array $attributes): IndoorsCollection
    {
        return IndoorsCollection::resolve($value);
    }

    /**
     * @throws JsonException
     */
    public function set($model, string $key, $value, array $attributes): string
    {
        return IndoorsCollection::getJson($value);
    }
}
