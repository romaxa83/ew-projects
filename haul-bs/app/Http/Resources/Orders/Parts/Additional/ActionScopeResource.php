<?php

namespace App\Http\Resources\Orders\Parts\Additional;

use App\Models\Orders\Parts\Order;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(schema="ActionScopeRaw", type="object", allOf={
 *      @OA\Schema(
 *          required={"can_update", "can_add_payment", "can_delete", "can_send_invoice", "can_refunded", "can_change_status", "can_canceled"},
 *          @OA\Property(property="can_update", type="boolean"),
 *          @OA\Property(property="can_add_payment", type="boolean"),
 *          @OA\Property(property="can_delete", type="boolean"),
 *          @OA\Property(property="can_send_invoice", type="boolean"),
 *          @OA\Property(property="can_refunded", type="boolean"),
 *          @OA\Property(property="can_change_status", type="boolean"),
 *          @OA\Property(property="can_canceled", type="boolean"),
 *          @OA\Property(property="can_assign_manager", type="boolean"),
 *      )
 * })
 *
 * @mixin Order
 */
class ActionScopeResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'can_update' => $this->canUpdate(),
            'can_add_payment' => $this->canAddPayment(),
            'can_delete' => $this->canDelete(),
            'can_send_invoice' => $this->canSendInvoice(),
            'can_refunded' => $this->canRefunded(),
            'can_change_status' => $this->canChangeStatus(),
            'can_canceled' => $this->canCanceled(),
            'can_assign_manager' => $this->canAssignManger(),
        ];
    }
}

