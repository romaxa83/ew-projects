<?php

namespace App\Http\Requests\Vehicles;

use App\Enums\Tags\TagType;
use App\Foundations\Http\Requests\BaseFormRequest;
use App\Models\Customers\Customer;
use App\Models\Tags\Tag;
use App\Models\Vehicles\Vehicle;
use Illuminate\Validation\Rule;

class VehicleFilterRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return array_merge(
            $this->paginationRule(),
            $this->searchRule(),
            $this->orderRule(Vehicle::ALLOWED_SORTING_FIELDS),
            [
                'tag_id' => ['nullable', 'integer',
                    Rule::exists(Tag::class, 'id')
                        ->where('type', TagType::TRUCKS_AND_TRAILER),
                ],
                'customer_id' => ['nullable', 'integer',
                    Rule::exists(Customer::class, 'id')
                ],
            ]
        );
    }
}
