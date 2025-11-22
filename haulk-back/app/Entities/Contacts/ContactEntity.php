<?php


namespace App\Entities\Contacts;


use App\Http\Resources\Orders\OrderContactResource;
use App\Models\DiffableInterface;
use Illuminate\Database\Eloquent\Concerns\HasAttributes;

class ContactEntity implements DiffableInterface
{
    private array $attributes;

    public function __construct(array $attributes = [])
    {
        $this->attributes = $attributes;
    }

    public function getAttributesForDiff(): array
    {
        return OrderContactResource::make($this->attributes)->toArray(request());
    }

    public function getRelationsForDiff(): array
    {
        return [];
    }
}
