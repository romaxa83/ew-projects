<?php

namespace App\Http\Resources\Orders;

use App\Http\Controllers\Api\Orders\PaymentMethodController;
use App\Models\Contacts\Contact;
use App\Models\Orders\Order;
use App\Http\Resources\Contacts\ContactResource;
use App\Http\Resources\Files\ImageResource;
use App\Http\Resources\Files\FileResource;
use App\Http\Resources\Orders\ExpenseListResource;
use App\Http\Resources\Orders\PaymentResource;
use App\Http\Resources\Orders\VehicleListResource;
use App\Http\Resources\Users\UserMiniResource;
use App\Models\Users\User;
use App\Services\Roles\RoleService;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyOrdersResource extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @OA\Schema(
     *    schema="CompanyOrderResource",
     *    type="object",
     *        allOf={
     *            @OA\Schema(
     *                @OA\Property(property="id", type="integer", description=""),
     *                @OA\Property(property="load_id", type="string", description=""),
     *                @OA\Property(property="status", type="integer", description=""),
     *                @OA\Property(property="state", type="string", description="Order state (new, assigned, pickedup, delivered, billed, paid, deleted)"),
     *                @OA\Property(property="delivery_contact", type="object", description="Order delivery contact", allOf={@OA\Schema(ref="#/components/schemas/ContactResourceRaw")}),
     *                @OA\Property(property="delivery_date_actual", type="integer", description=""),
     *                @OA\Property(property="shipper_full_name", type="string", description=""),
     *                @OA\Property(property="shipper_phone", type="string", description=""),
     *                @OA\Property(property="shipper_phone_extension", type="string", description=""),
     *                @OA\Property(property="is_overdue", type="boolean", description=""),
     *                @OA\Property(property="is_billed", type="boolean", description=""),
     *                @OA\Property(property="is_paid", type="boolean", description=""),
     *                @OA\Property(property="payment", type="object", description="Order payment", allOf={@OA\Schema(ref="#/components/schemas/PaymentResourceRaw")}),
     *            )
     *        }
     * )
     *
     * @OA\Schema(
     *    schema="CompanyOrdersResource",
     *    @OA\Property(
     *        property="data",
     *        description="Orders paginated list",
     *        type="array",
     *        @OA\Items(ref="#/components/schemas/CompanyOrderResource")
     *    ),
     * )
     *
     */
    public function toArray($request)
    {
        $delivery_contact = new Contact($this->delivery_contact ? $this->delivery_contact : []);
        $shipper_contact = new Contact($this->shipper_contact ? $this->shipper_contact : []);

        return [
            'id' => (int) $this->id,
            'load_id' => $this->load_id,
            'status' => (int) $this->status,
            'state' => $this->getStateForCrm(),
            'delivery_contact' => $delivery_contact,
            'delivery_date_actual' => $this->delivery_date_actual ? (int) $this->delivery_date_actual : null,
            'shipper_full_name' => $this->shipper_full_name,
            'shipper_phone' => $shipper_contact ? $shipper_contact->phone : null,
            'shipper_phone_extension' => $shipper_contact ? $shipper_contact->phone_extension : null,
            'is_overdue' => $this->isPaymentOverdue(),
            'is_billed' => $this->is_billed,
            'is_paid' => ($this->paid_at !== null),
            'payment' => new PaymentResource($this->payment),
        ];
    }
}
