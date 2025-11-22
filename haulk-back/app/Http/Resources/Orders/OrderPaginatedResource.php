<?php

namespace App\Http\Resources\Orders;

use App\Http\Resources\Contacts\ContactResource;
use App\Http\Resources\Tags\TagShortResource;
use App\Http\Resources\Users\UserMiniResource;
use App\Models\Contacts\Contact;
use App\Models\Orders\Order;
use App\Services\Orders\OrderSearchService;
use App\Services\Orders\OrderService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * @mixin Order
 */
class OrderPaginatedResource extends JsonResource
{
    /**
     * @OA\Schema(schema="OrderPaginatedResource",
     *    @OA\Property(property="data", description="Orders paginated list", type="array",
     *        @OA\Items(ref="#/components/schemas/OrderResource")
     *    ),
     *    @OA\Property(property="links", ref="#/components/schemas/PaginationLinks"),
     *    @OA\Property(property="meta", ref="#/components/schemas/PaginationMeta"),
     * )
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        return array_merge(
            [
                'id' => (int)$this->id,
                'user_id' => (int)$this->user_id,
                'user' => UserMiniResource::make($this->user),
                'driver_id' => $this->driver_id ? (int)$this->driver_id : null,
                'driver' => UserMiniResource::make($this->driver),
                'dispatcher_id' => $this->dispatcher_id ? (int)$this->dispatcher_id : null,
                'dispatcher' => UserMiniResource::make($this->dispatcher),
                'load_id' => $this->load_id,
                'inspection_type' => $this->getInspectionType(),
                'status' => $this->status,
                'state' => $this->getStateForCrm(),
                'need_review' => $this->need_review,
                'has_review' => $this->has_review,
                'instructions' => $this->instructions,
                'dispatch_instructions' => $this->dispatch_instructions,
                'pickup_contact' => ContactResource::make(Contact::make($this->pickup_contact ?? [])),
                'pickup_date' => $this->pickup_date ? (int)$this->pickup_date : null,
                'pickup_date_actual' => $this->pickup_date_actual ? (int)$this->pickup_date_actual : null,
                'is_manual_change_to_pickup' => $this->is_manual_change_to_pickup,

                'pickup_buyer_name_number' => $this->pickup_buyer_name_number,

                'pickup_time' => $this->pickup_time,
                'delivery_contact' => ContactResource::make(Contact::make($this->delivery_contact ?? [])),
                'delivery_date' => $this->delivery_date ? (int)$this->delivery_date : null,
                'delivery_date_actual' => $this->delivery_date_actual
                    ? (int)$this->delivery_date_actual
                    : null,
                'is_manual_change_to_delivery' => $this->is_manual_change_to_delivery,

                'delivery_buyer_number' => $this->delivery_buyer_number, //OLD
                'delivery_buyer_name_number' => $this->delivery_buyer_number,

                'delivery_time' => $this->delivery_time,
                'vehicles' => VehicleListResource::collection($this->vehicles),
                'payment_for_miles' => $this->getPaymentForMiles(),
                'payment' => PaymentResource::make($this->payment),
                'shipper_contact' => ContactResource::make(Contact::make($this->shipper_contact ?? [])),
                'expenses' => ExpenseListResource::collection($this->expenses),
                'bonuses' => BonusListResource::collection($this->bonuses),

                'has_pickup_inspection' => $this->has_pickup_inspection,
                'has_pickup_signature' => $this->has_pickup_signature,
                'has_delivery_inspection' => $this->has_delivery_inspection,
                'has_delivery_signature' => $this->has_delivery_signature,

                'is_billed' => $this->is_billed === true,
                'is_deleted' => $this->trashed(),

                'pickup_customer_not_available' => $this->pickup_customer_not_available,
                'pickup_customer_refused_to_sign' => $this->pickup_customer_refused_to_sign,
                'pickup_customer_full_name' => $this->pickup_customer_full_name,

                'deduct_from_driver' => $this->deduct_from_driver,
                'deducted_note' => $this->deducted_note,

                'delivery_customer_not_available' => $this->delivery_customer_not_available,
                'delivery_customer_refused_to_sign' => $this->delivery_customer_refused_to_sign,
                'delivery_customer_full_name' => $this->delivery_customer_full_name,
                'public_token' => $this->public_token,
                'seen_by_driver' => $this->seen_by_driver,

                'allowed_status_change' => $this->getAllowedStatusChangeList(),

                'need_signature' => resolve(OrderService::class)->necessarySendSignatureLink(
                    $this->resource,
                    $request->user()
                ),

                'last_note' => $this->comments->isNotEmpty() ? $this->comments[0]->comment : null,
                /**@see Order::getManyRelations() - comments relation */
                'comments_count' => $this->comments->isNotEmpty() ? $this->comments[0]->count : 0,

                'payroll' => $this->payrolls->isNotEmpty() ? [
                    'id' => $this->payrolls[0]->id,
                    'start' => $this->payrolls[0]->start->timestamp,
                    'end' => $this->payrolls[0]->end->timestamp,
                    'is_salary_paid' => $this->payrolls[0]->is_paid,
                ] : null,

                'created_at' => $this->created_at->timestamp,
                'tags' => TagShortResource::collection($this->tags),
                'last_payment_stage' => PaymentStageResource::make(
                    $this->paymentStages->isNotEmpty() ? $this->paymentStages->first() : null
                )
            ],
            /**@see OrderSearchService::addResourceInfoForOrder() */
            $this->getAttribute('resource_info')
        );
    }
}
