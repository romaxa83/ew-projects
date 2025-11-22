<?php

namespace App\Http\Resources\Customers;

use App\Http\Resources\Tags\TagShortResource;
use App\Http\Resources\Users\UserShortListResource;
use App\Models\Customers\Customer;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(schema="CustomerRaw", type="object",
 *     @OA\Property(property="data", type="object", description="Customer data", allOf={
 *         @OA\Schema(
 *             required={"id", "first_name", "last_name", "phone", "email"},
 *             @OA\Property(property="id", type="integer", description="Customer id"),
 *             @OA\Property(property="type", type="string", description="Customer type", enum={"bs", "ecomm", "haulk"}),
 *             @OA\Property(property="first_name", type="string", description="Customer first_name"),
 *             @OA\Property(property="last_name", type="string", description="Customer last_name"),
 *             @OA\Property(property="email", type="string", description="Customer email"),
 *             @OA\Property(property="phone", type="string", description="Customer phone"),
 *             @OA\Property(property="phone_extension", type="string", description="Customer phone extension"),
 *             @OA\Property(property="tags", type="array", description="Vehicle Owner tags",
 *                 @OA\Items(ref="#/components/schemas/TagRawShort")
 *             ),
 *             @OA\Property(property="hasRelatedTrucks", type="boolean", description="Is Customer has related trucks"),
 *             @OA\Property(property="hasRelatedTrailers", type="boolean", description="Is Customer has related trailers"),
 *             @OA\Property(property="hasRelatedPartOrders", type="boolean", description="Is Customer can be deleted"),
 *             @OA\Property(property="comments_count", type="integer", description="Comments count"),
 *             @OA\Property(property="sales_manager", ref="#/components/schemas/UserRawShort"),
 *         )
 *      }),
 *  )
 *
 * @OA\Schema(schema="CustomerPaginationResource",
 *      @OA\Property(property="data", description="Customer paginated list",type="array",
 *          @OA\Items(ref="#/components/schemas/CustomerRaw")
 *      ),
 *      @OA\Property(property="links", ref="#/components/schemas/PaginationLinks",),
 *      @OA\Property(property="meta", ref="#/components/schemas/PaginationMeta",),
 *  )
 *
 * @mixin Customer
 */
class CustomerPaginationResource extends JsonResource
{

    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email->getValue(),
            'phone' => $this->phone?->getValue(),
            'phone_extension' => $this->phone_extension,
            'tags' => TagShortResource::collection($this->tags),
            'hasRelatedTrucks' => $this->trucks()->exists(),
            'hasRelatedTrailers' => $this->trailers()->exists(),
            'hasRelatedPartOrders' => $this->hasRelatedPartOrders(),
            'comments_count' => $this->comments->count(),
            'sales_manager' => UserShortListResource::make($this->salesManager),
        ];
    }
}
