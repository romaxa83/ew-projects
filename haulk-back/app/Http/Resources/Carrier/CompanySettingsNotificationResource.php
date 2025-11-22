<?php

namespace App\Http\Resources\Carrier;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanySettingsNotificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     *
     * @OA\Schema(schema="CompanySettingsNotificationResourceRaw", type="object", allOf={
     *      @OA\Schema(
     *          @OA\Property(property="notification_emails", type="array", description="Receive pickup/delivery notifications. Enter multiple emails with comma.", @OA\Items(
     *                  type="object",
     *                  allOf={
     *                      @OA\Schema(
     *                          @OA\Property(property="value", type="string",),
     *                      )
     *                  }
     *              ),),
     *          @OA\Property(property="receive_bol_copy_emails", type="array", description="Receive copy of BOLs for all delivered loads. Enter multiple emails with comma.", @OA\Items(
     *                  type="object",
     *                  allOf={
     *                      @OA\Schema(
     *                          @OA\Property(property="value", type="string",),
     *                      )
     *                  }
     *              ),),
     *          @OA\Property(property="brokers_delivery_notification", type="boolean", description="Enable sending pickup/delivery notifications to brokers."),
     *          @OA\Property(property="add_pickup_delivery_dates_to_bol", type="boolean", description="Add pickup/delivery dates to BOL"),
     *          @OA\Property(property="send_bol_invoice_automatically", type="boolean", description="Send BOL/invoice automaticaly or manualy by click on button."),
     *      )
     *   }
     * )
     *
     * @OA\Schema(schema="CompanySettingsNotificationResource", type="object",
     *     @OA\Property(property="data", type="object", description="Contact data", allOf={
     *          @OA\Schema(
     *              @OA\Property(property="notification_emails", type="array", description="Receive pickup/delivery notifications. Enter multiple emails with comma.", @OA\Items(
     *                  type="object",
     *                  allOf={
     *                      @OA\Schema(
     *                          @OA\Property(property="value", type="string",),
     *                      )
     *                  }
     *              ),),
     *              @OA\Property(property="receive_bol_copy_emails", type="array", description="Receive copy of BOLs for all delivered loads. Enter multiple emails with comma.", @OA\Items(
     *                  type="object",
     *                  allOf={
     *                      @OA\Schema(
     *                          @OA\Property(property="value", type="string",),
     *                      )
     *                  }
     *              ),),
     *              @OA\Property(property="brokers_delivery_notification", type="boolean", description="Enable sending pickup/delivery notifications to brokers."),
     *              @OA\Property(property="add_pickup_delivery_dates_to_bol", type="boolean", description="Add pickup/delivery dates to BOL"),
     *              @OA\Property(property="send_bol_invoice_automatically", type="boolean", description="Send BOL/invoice automaticaly or manualy by click on button."),
     *              @OA\Property(property="is_invoice_allowed", type="boolean", description="Есть у водителей возможность отправки invoice"),
     *          )
     *       }
     *     ),
     * )
     */

    public function toArray($request)
    {
        return [
            'notification_emails' => $this->notification_emails,
            'receive_bol_copy_emails' => $this->receive_bol_copy_emails,
            'brokers_delivery_notification' => (bool) $this->brokers_delivery_notification,
            'add_pickup_delivery_dates_to_bol' => (bool) $this->add_pickup_delivery_dates_to_bol,
            'send_bol_invoice_automatically' => (bool) $this->send_bol_invoice_automatically,
            'is_invoice_allowed' => (bool) $this->is_invoice_allowed,
        ];
    }
}
