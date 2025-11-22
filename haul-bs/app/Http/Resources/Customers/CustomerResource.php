<?php

namespace App\Http\Resources\Customers;

use App\Http\Resources\Files\FileResource;
use App\Http\Resources\Tags\TagShortResource;
use App\Http\Resources\Users\UserShortListResource;
use App\Http\Resources\Vehicles\VehicleForCustomerResource;
use App\Models\Customers\Customer;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(schema="CustomerResource", type="object",
 *     @OA\Property(property="data", type="object", description="Customer data", allOf={
 *         @OA\Schema(
 *             required={"id", "fisrt_name", "last_name", "phone", "email"},
 *             @OA\Property(property="id", type="integer", description="Customer id"),
 *             @OA\Property(property="first_name", type="string", description="Customer first name"),
 *             @OA\Property(property="last_name", type="string", description="Customer last name"),
 *             @OA\Property(property="email", type="string", description="Customer email"),
 *             @OA\Property(property="phone", type="string", description="Customer phone"),
 *             @OA\Property(property="phone_extension", type="string", description="Customer phone extension"),
 *             @OA\Property(property="phones", type="array", description="Customer aditional phones",
 *                 @OA\Items(ref="#/components/schemas/PhonesRaw")
 *             ),
 *             @OA\Property(property="notes", type="string", description="Customer notes"),
 *             @OA\Property(property="tags", type="array", description="Customer tags",
 *                 @OA\Items(ref="#/components/schemas/TagRawShort")
 *             ),
 *             @OA\Property(property="attachments", type="array", description="Customer attachments",
 *                 @OA\Items(ref="#/components/schemas/FileRaw")
 *             ),
 *             @OA\Property(property="hasRelatedEntities", type="boolean", description="Is Customer can be deleted"),
 *             @OA\Property(property="hasRelatedPartOrders", type="boolean", description="Is Customer can be deleted"),
 *             @OA\Property(property="trucks", type="array", description="Customer trucks",
 *                 @OA\Items(ref="#/components/schemas/VehicleForCustomerResource")
 *             ),
 *             @OA\Property(property="trailers", type="array", description="Customer trailers",
 *                 @OA\Items(ref="#/components/schemas/VehicleForCustomerResource")
 *             ),
 *             @OA\Property(property="type", type="string", enum={"bs", "ecomm", "haulk"}),
 *             @OA\Property(property="taxExemption", ref="#/components/schemas/CustomerTaxExemptionResource"),
 *             @OA\Property(property="addresses", type="array", description="Customer delivery addresses",
 *                 @OA\Items(ref="#/components/schemas/AddressRawResource")
 *             ),
 *             @OA\Property(property="sales_manager", ref="#/components/schemas/UserRawShort"),
 *             @OA\Property(property="has_ecommerce_account", type="boolean", description="has ecommerce account"),
 *         )}
 *     ),
 * )
 *
 * @mixin Customer
 */
class CustomerResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email->getValue(),
            'phone' => $this->phone?->getValue(),
            'phone_extension' => $this->phone_extension,
            'phones' => $this->phones ?? [],
            'notes' => $this->notes,
            Customer::ATTACHMENT_COLLECTION_NAME => FileResource::collection($this->getAttachments()),
            'tags' => TagShortResource::collection($this->tags),
            'hasRelatedEntities' => $this->hasRelatedEntities(),
            'hasRelatedPartOrders' => $this->hasRelatedPartOrders(),
            'trucks' => VehicleForCustomerResource::collection($this->trucks),
            'trailers' => VehicleForCustomerResource::collection($this->trailers),
            'type' => $this->type->value,
            'addresses' => AddressResource::collection($this->addresses),
            'taxExemption' => CustomerTaxExemptionResource::make($this->taxExemption),
            'sales_manager' => UserShortListResource::make($this->salesManager),
            'has_ecommerce_account' => $this->has_ecommerce_account,
        ];
    }
}
