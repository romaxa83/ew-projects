<?php

namespace App\Http\Resources\Customers;

use App\Http\Resources\Tags\TagShortResource;
use App\Models\Customers\Customer;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(schema="CustomerShortRaw", type="object",
 *     @OA\Property(property="data", type="object", description="Customer data", allOf={
 *         @OA\Schema(
 *             required={"id", "first_name", "last_name", "phone", "email"},
 *             @OA\Property(property="id", type="integer", description="Customer id"),
 *             @OA\Property(property="first_name", type="string", description="Customer first_name"),
 *             @OA\Property(property="last_name", type="string", description="Customer last_name"),
 *             @OA\Property(property="email", type="string", description="Customer email"),
 *             @OA\Property(property="phone", type="string", description="Customer phone"),
 *             @OA\Property(property="phone_extension", type="string", description="Customer phone extension"),
 *             @OA\Property(property="type", type="string", enum={"bs", "ecomm", "haulk"}),
 *             @OA\Property(property="tags", type="array", description="Vehicle Owner tags",
 *                 @OA\Items(ref="#/components/schemas/TagRawShort")
 *             ),
 *         )
 *      }),
 *  )
 *
 * @OA\Schema(schema="CustomerShortPaginationResource",
 *      @OA\Property(property="data", description="Customer paginated list",type="array",
 *          @OA\Items(ref="#/components/schemas/CustomerShortRaw")
 *      ),
 *      @OA\Property(property="links", ref="#/components/schemas/PaginationLinks",),
 *      @OA\Property(property="meta", ref="#/components/schemas/PaginationMeta",),
 *  )
 *
 * @mixin Customer
 */
class CustomerShortPaginationResource extends JsonResource
{

    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'phone' => $this->phone?->getValue(),
            'phone_extension' => $this->phone_extension,
            'email' => $this->email->getValue(),
            'type' => $this->type->value,
            'tags' => TagShortResource::collection($this->tags),
        ];
    }
}
