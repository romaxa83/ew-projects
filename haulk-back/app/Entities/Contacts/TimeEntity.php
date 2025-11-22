<?php


namespace App\Entities\Contacts;


use App\Http\Resources\Orders\OrderContactResource;
use App\Http\Resources\Orders\OrderTimeResource;
use App\Models\DiffableInterface;
use Illuminate\Database\Eloquent\Concerns\HasAttributes;

class TimeEntity implements DiffableInterface
{
    private array $attributes;

    public function __construct(array $attributes = [])
    {
        $this->attributes = $attributes;
    }

    public function getAttributesForDiff(): array
    {
        return OrderTimeResource::make($this->attributes)->toArray(request());
    }

    public function getRelationsForDiff(): array
    {
        return [];
    }
}
