<?php

namespace App\Http\Resources\Orders;

use App\Http\Resources\Contacts\ContactResource;
use App\Http\Resources\Files\FileResource;
use App\Http\Resources\Files\ImageResource;
use App\Http\Resources\Tags\TagShortResource;
use App\Http\Resources\Users\UserMiniResource;
use App\Http\Resources\Users\UserResource;
use App\Models\Contacts\Contact;
use App\Models\Orders\Order;
use App\Services\Orders\OrderSearchService;
use App\Services\Orders\OrderService;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     *
     * @OA\Schema(
     *    schema="OrderResourceRaw",
     *    type="object",
     *        allOf={
     *            @OA\Schema(
     *                @OA\Property(property="id", type="integer", description="Order id"),
     *                @OA\Property(property="user_id", type="integer", description="Order creator id"),
     *                @OA\Property(property="user", type="object", description="Order creator", allOf={@OA\Schema(ref="#/components/schemas/UserMini")}),
     *                @OA\Property(property="driver_id", type="integer", description="Order driver id"),
     *                @OA\Property(property="driver", type="object", description="Order driver", allOf={@OA\Schema(ref="#/components/schemas/User")}),
     *                @OA\Property(property="dispatcher_id", type="integer", description="Order dispatcher id"),
     *                @OA\Property(property="dispatcher", type="object", description="Order dispatcher", allOf={@OA\Schema(ref="#/components/schemas/UserMini")}),
     *                @OA\Property(property="load_id", type="string", description="Order load id"),
     *                @OA\Property(property="internal_load_id", type="string", description="Order internal load id"),
     *                @OA\Property(property="status", type="integer", description="Order status"),
     *                @OA\Property(property="order_category", type="string", description="Order category for mobile app"),
     *                @OA\Property(property="state", type="string", description="Order state (new, assigned, pickedup, delivered, billed, paid, deleted)"),
     *                @OA\Property(property="need_review", type="boolean", description="Order needs review flag"),
     *                @OA\Property(property="has_review", type="boolean", description="Order was reviewed flag"),
     *                @OA\Property(property="instructions", type="string", description="Order instructions"),
     *                @OA\Property(property="dispatch_instructions", type="string", description="Order instructions"),
     *                @OA\Property(property="pickup_contact", type="object", description="Order pickup contact", allOf={@OA\Schema(ref="#/components/schemas/ContactResourceRaw")}),
     *                @OA\Property(property="pickup_date", type="integer", description="Order pickup date timestamp"),
     *                @OA\Property(property="pickup_date_actual", type="integer", description="Actual order pickup date timestamp"),
     *                @OA\Property(property="pickup_buyer_name_number", type="string", description="Buyer name/number"),
     *                @OA\Property(property="pickup_schedule_timeline", type="string", description="Pickup schedule timeline"),
     *                @OA\Property(property="delivery_contact", type="object", description="Order delivery contact", allOf={@OA\Schema(ref="#/components/schemas/ContactResourceRaw")}),
     *                @OA\Property(property="delivery_date", type="integer", description="Order delivery date timestamp"),
     *                @OA\Property(property="delivery_date_actual", type="integer", description="Actual order delivery date timestamp"),
     *                @OA\Property(property="delivery_buyer_number", type="integer", description="Delivery buyer number", deprecated="true"),
     *                @OA\Property(property="delivery_buyer_name_number", type="integer", description="Delivery buyer number"),
     *                @OA\Property(property="delivery_schedule_timeline", type="integer", description="Delivery schedule timeline"),
     *                @OA\Property(property="vehicles", type="array", description="Order vehicles", @OA\Items(ref="#/components/schemas/VehicleResourceRaw")),
     *                @OA\Property(property="payment", type="object", description="Order payment", allOf={@OA\Schema(ref="#/components/schemas/PaymentResourceRaw")}),
     *                @OA\Property(property="payment_stages", type="array", description="Order payment stages", @OA\Items(ref="#/components/schemas/PaymentStageResourceRaw")),
     *                @OA\Property(property="shipper_contact", type="object", description="Order shipper contact", allOf={@OA\Schema(ref="#/components/schemas/ContactResourceRaw")}),
     *                @OA\Property(property="expenses", type="array", description="Order expenses", @OA\Items(ref="#/components/schemas/ExpenseResourceRaw")),
     *                @OA\Property(property="bonuses", type="array", description="Order bonuses", @OA\Items(ref="#/components/schemas/BonusResourceRaw")),
     *                @OA\Property(property="has_pickup_inspection", type="boolean",),
     *                @OA\Property(property="has_pickup_signature", type="boolean",),
     *                @OA\Property(property="has_delivery_inspection", type="boolean",),
     *                @OA\Property(property="has_delivery_signature", type="boolean",),
     *                @OA\Property(property="is_billed", type="boolean",),
     *                @OA\Property(property="is_paid", type="boolean",),
     *                @OA\Property(property="is_deleted", type="boolean",),
     *                @OA\Property(property="is_overdue", type="boolean",),
     *                @OA\Property(property="pickup_customer_not_available", type="boolean",),
     *                @OA\Property(property="pickup_customer_full_name", type="string",),
     *                @OA\Property(property="pickup_customer_signature", type="object", @OA\Schema(ref="#/components/schemas/File")),
     *                @OA\Property(property="pickup_driver_signature", type="object", @OA\Schema(ref="#/components/schemas/File")),
     *                @OA\Property(property="delivery_customer_not_available", type="boolean",),
     *                @OA\Property(property="delivery_customer_full_name", type="string",),
     *                @OA\Property(property="delivery_customer_signature", type="object", @OA\Schema(ref="#/components/schemas/File")),
     *                @OA\Property(property="delivery_driver_signature", type="object", @OA\Schema(ref="#/components/schemas/File")),
     *                @OA\Property(property="attachments", type="array", description="Order attachments", @OA\Items(ref="#/components/schemas/File")),
     *                @OA\Property(property="documents", type="array", description="Driver documents", @OA\Items(ref="#/components/schemas/File")),
     *                @OA\Property(property="photos", type="array", description="Driver photos", @OA\Items(ref="#/components/schemas/File")),
     *                @OA\Property(property="driver_comments", type="array", description="Driver comments", @OA\Items(ref="#/components/schemas/OrderCommentResourceRaw")),
     *                @OA\Property(property="public_token", type="string", description="Order token to access the order from public link",),
     *                @OA\Property(property="need_signature", type="array", description="Necessary signatures", nullable=true, @OA\Items(type="string", enum={"pickup","delivery"})),
     *                @OA\Property(property="created_at", type="integer", description=""),
     *                @OA\Property(property="tags", type="array", description="Order tags", @OA\Items(ref="#/components/schemas/TagRawShort")),
     *            )
     *        }
     * )
     *
     * @OA\Schema(
     *    schema="OrderResource",
     *    type="object",
     *        @OA\Property(
     *            property="data",
     *            type="object",
     *            description="Order data",
     *            allOf={
     *                  @OA\Schema(
     *                      @OA\Property(property="id", type="integer", description="Order id"),
     *                      @OA\Property(property="user_id", type="integer", description="Order creator id"),
     *                      @OA\Property(property="user", type="object", description="Order creator", allOf={@OA\Schema(ref="#/components/schemas/UserMini")}),
     *                      @OA\Property(property="driver_id", type="integer", description="Order driver id"),
     *                      @OA\Property(property="driver", type="object", description="Order driver", allOf={@OA\Schema(ref="#/components/schemas/User")}),
     *                      @OA\Property(property="dispatcher_id", type="integer", description="Order dispatcher id"),
     *                      @OA\Property(property="dispatcher", type="object", description="Order dispatcher", allOf={@OA\Schema(ref="#/components/schemas/User")}),
     *                      @OA\Property(property="load_id", type="string", description="Order load id"),
     *                      @OA\Property(property="internal_load_id", type="string", description="Order internal load id"),
     *                      @OA\Property(property="status", type="integer", description="Order status"),
     *                      @OA\Property(property="order_category", type="string", description="Order category for mobile app"),
     *                      @OA\Property(property="state", type="string", description="Order state (new, assigned, pickedup, delivered, billed, paid, deleted)"),
     *                      @OA\Property(property="need_review", type="boolean", description="Order needs review flag"),
     *                      @OA\Property(property="has_review", type="boolean", description="Order was reviewed flag"),
     *                      @OA\Property(property="instructions", type="string", description="Order instructions"),
     *                      @OA\Property(property="dispatch_instructions", type="string", description="Order instructions"),
     *                      @OA\Property(property="pickup_contact", type="object", description="Order pickup contact", allOf={@OA\Schema(ref="#/components/schemas/ContactResourceRaw")}),
     *                      @OA\Property(property="pickup_date", type="integer", description="Order pickup date timestamp"),
     *                      @OA\Property(property="pickup_date_actual", type="integer", description="Actual order pickup date timestamp"),
     *                      @OA\Property(property="is_manual_change_to_pickup", type="boolean"),
     *                      @OA\Property(property="pickup_buyer_name_number", type="string", description="Buyer name/number"),
     *                      @OA\Property(property="pickup_schedule_timeline", type="string", description="Pickup schedule timeline"),
     *                      @OA\Property(property="pickup_comment", type="string", description="Pickup comment"),
     *                      @OA\Property(property="delivery_contact", type="object", description="Order delivery contact", allOf={@OA\Schema(ref="#/components/schemas/ContactResourceRaw")}),
     *                      @OA\Property(property="delivery_date", type="integer", description="Order delivery date timestamp"),
     *                      @OA\Property(property="delivery_date_actual", type="integer", description="Actual order delivery date timestamp"),
     *                      @OA\Property(property="is_manual_change_to_delivery", type="boolean"),
     *                      @OA\Property(property="delivery_buyer_number", type="integer", description="Delivery buyer number"),
     *                      @OA\Property(property="delivery_schedule_timeline", type="integer", description="Delivery schedule timeline"),
     *                      @OA\Property(property="delivery_comment", type="string", description="Delivery comment"),
     *                      @OA\Property(property="vehicles", type="array", description="Order vehicles", @OA\Items(ref="#/components/schemas/VehicleResourceRaw")),
     *                      @OA\Property(property="payment_for_miles", type="number"),
     *                      @OA\Property(property="payment", type="object", description="Order payment", allOf={@OA\Schema(ref="#/components/schemas/PaymentResourceRaw")}),
     *                      @OA\Property(property="payment_stages", type="array", description="Order payment stages", @OA\Items(ref="#/components/schemas/PaymentStageResourceRaw")),
     *                      @OA\Property(property="shipper_contact", type="object", description="Order shipper contact", allOf={@OA\Schema(ref="#/components/schemas/ContactResourceRaw")}),
     *                      @OA\Property(property="shipper_comment", type="string", description="Shipper comment"),
     *                      @OA\Property(property="expenses", type="array", description="Order expenses", @OA\Items(ref="#/components/schemas/ExpenseResourceRaw")),
     *                      @OA\Property(property="bonuses", type="array", description="Order expenses", @OA\Items(ref="#/components/schemas/BonusResourceRaw")),
     *                      @OA\Property(property="has_pickup_inspection", type="boolean",),
     *                      @OA\Property(property="has_pickup_signature", type="boolean",),
     *                      @OA\Property(property="has_delivery_inspection", type="boolean",),
     *                      @OA\Property(property="has_delivery_signature", type="boolean",),
     *                      @OA\Property(property="is_billed", type="boolean",),
     *                      @OA\Property(property="is_paid", type="boolean",),
     *                      @OA\Property(property="is_deleted", type="boolean",),
     *                      @OA\Property(property="deduct_from_driver", type="boolean",),
     *                      @OA\Property(property="deducted_note", type="string", nullable="true"),
     *                      @OA\Property(property="is_overdue", type="boolean",),
     *                      @OA\Property(property="overdue", type="object", allOf={
     *                          @OA\Schema(schema="OverdueRaw", type="object",
     *                              @OA\Property(property="type", type="string", example="pickup|delviery|payment"),
     *                              @OA\Property(property="message", type="string", example="Some text message"),
     *                          )
     *                      }),
     *                      @OA\Property(property="pickup_customer_not_available", type="boolean",),
     *                      @OA\Property(property="pickup_customer_full_name", type="string",),
     *                      @OA\Property(property="pickup_customer_signature", type="object", @OA\Schema(ref="#/components/schemas/File")),
     *                      @OA\Property(property="pickup_driver_signature", type="object", @OA\Schema(ref="#/components/schemas/File")),
     *                      @OA\Property(property="delivery_customer_not_available", type="boolean",),
     *                      @OA\Property(property="delivery_customer_full_name", type="string",),
     *                      @OA\Property(property="delivery_customer_signature", type="object", @OA\Schema(ref="#/components/schemas/File")),
     *                      @OA\Property(property="delivery_driver_signature", type="object", @OA\Schema(ref="#/components/schemas/File")),
     *                      @OA\Property(property="attachments", type="array", description="Order attachments", @OA\Items(ref="#/components/schemas/FileRaw")),
     *                      @OA\Property(property="documents", type="array", description="Driver documents", @OA\Items(ref="#/components/schemas/FileRaw")),
     *                      @OA\Property(property="photos", type="array", description="Driver photos", @OA\Items(ref="#/components/schemas/FileRaw")),
     *                      @OA\Property(property="driver_comments", type="array", description="Driver comments", @OA\Items(ref="#/components/schemas/OrderCommentResourceRaw")),
     *                      @OA\Property(property="need_signature", type="array", description="Necessary signatures", nullable=true, @OA\Items(type="string", enum={"pickup","delivery"})),
     *                      @OA\Property(property="public_token", type="string", description="Order token to access the order from public link",),
     *                      @OA\Property(property="payroll", type="object", description="", nullable="true", allOf={
     *                            @OA\Schema (
     *                                @OA\Property(property="id", type="integer", description=""),
     *                                @OA\Property(property="start", type="integer", description=""),
     *                                @OA\Property(property="end", type="integer", description=""),
     *                                @OA\Property(property="is_salary_paid", type="boolean", description=""),
     *                            )
     *                      }),
     *                      @OA\Property(property="created_at", type="integer", description=""),
     *                      @OA\Property(property="broker_fee_total_due", type="number", format="float", description="Broker Fee total due"),
     *                      @OA\Property(property="broker_fee_past_due", type="number", format="float", description="Broker Fee past due"),
     *                      @OA\Property(property="broker_fee_current_due", type="number", format="float", description="Broker Fee current due"),
     *                      @OA\Property(property="last_payment_stage", type="object", nullable=true, description="Order last payment stages", @OA\Schema (ref="#/components/schemas/PaymentStageResourceRaw")),
     *                      @OA\Property(property="tags", type="array", description="Order tags", @OA\Items(ref="#/components/schemas/TagRawShort")),
     *                  )
     *            }
     *        ),
     * )
     *
     */
    public function toArray($request): array
    {
        /**@var Order $order */
        $order = $this->resource;

        $photos = $order->getMedia(Order::DRIVER_PHOTOS_COLLECTION_NAME);

        if ($photos->isNotEmpty()) {
            $photoResource = isAdminPanel() ? FileResource::collection($photos) : ImageResource::collection($photos);
        }
        if (empty($order->getAttribute('resource_info'))) {
            $order = resolve(OrderSearchService::class)->loadMissingResourceData($order);
        }

        return array_merge(
            [
                'id' => (int)$order->id,
                'user_id' => (int)$order->user_id,
                'user' => UserMiniResource::make($order->user),
                'driver_id' => $order->driver_id ? (int)$order->driver_id : null,
                'driver' => UserResource::make($order->driver),
                'dispatcher_id' => $order->dispatcher_id ? (int)$order->dispatcher_id : null,
                'dispatcher' => UserResource::make($order->dispatcher),
                'load_id' => $order->load_id,
                'inspection_type' => $order->getInspectionType(),
                'status' => $order->status,
                'state' => $order->getStateForCrm(),
                'need_review' => $order->need_review,
                'has_review' => $order->has_review,
                'instructions' => $order->instructions,
                'dispatch_instructions' => $order->dispatch_instructions,

                'pickup_contact' => ContactResource::make(Contact::make($order->pickup_contact ?? [])),
                'pickup_date' => $order->pickup_date ? (int)$order->pickup_date : null,
                'pickup_date_actual' => $order->pickup_date_actual ? (int)$order->pickup_date_actual : null,
                'pickup_date_actual_as_str' => $order->pickup_date_actual
                    ? CarbonImmutable::createFromTimestamp($order->pickup_date_actual)->format('Y-m-d H:i:s')
                    : null,
                'is_manual_change_to_pickup' => $order->is_manual_change_to_pickup,
                'pickup_buyer_name_number' => $order->pickup_buyer_name_number,
                'pickup_time' => $order->pickup_time,
                'pickup_comment' => $order->pickup_comment,

                'delivery_contact' => ContactResource::make(Contact::make($order->delivery_contact ?? [])),
                'delivery_date' => $order->delivery_date ? (int)$order->delivery_date : null,
                'delivery_date_actual' => $order->delivery_date_actual ? (int)$order->delivery_date_actual : null,
                'delivery_date_actual_as_str' => $order->delivery_date_actual
                    ? CarbonImmutable::createFromTimestamp($order->delivery_date_actual)->format('Y-m-d H:i:s')
                    : null,
                'is_manual_change_to_delivery' => $order->is_manual_change_to_delivery,
                'delivery_buyer_number' => $order->delivery_buyer_number, //old
                'delivery_buyer_name_number' => $order->delivery_buyer_number,
                'delivery_time' => $order->delivery_time,
                'delivery_comment' => $order->delivery_comment,

                'vehicles' => VehicleListResource::collection($order->vehicles),
                'payment_for_miles' => $order->getPaymentForMiles(),
                'payment' => PaymentResource::make($order->payment),
                'payment_stages' => PaymentStageResource::collection($order->paymentStages),
                'shipper_contact' => ContactResource::make(Contact::make($order->shipper_contact ?? [])),
                'shipper_comment' => $order->shipper_comment,
                'expenses' => ExpenseListResource::collection($order->expenses),
                'bonuses' => BonusListResource::collection($order->bonuses),

                'has_pickup_inspection' => $order->has_pickup_inspection,
                'has_pickup_signature' => $order->has_pickup_signature,
                'has_delivery_inspection' => $order->has_delivery_inspection,
                'has_delivery_signature' => $order->has_delivery_signature,

                'is_billed' => $order->is_billed === true,
                'is_deleted' => $order->trashed(),

                'deduct_from_driver' => (bool)$order->deduct_from_driver,
                'deducted_note' => $order->deducted_note,

                'pickup_customer_not_available' => $order->pickup_customer_not_available,
                'pickup_customer_refused_to_sign' => $order->pickup_customer_refused_to_sign,
                'pickup_customer_full_name' => $order->pickup_customer_full_name,
                'pickup_customer_signature' => FileResource::make(
                    $order->getFirstMedia(Order::PICKUP_CUSTOMER_SIGNATURE_COLLECTION_NAME)
                ),
                'pickup_driver_signature' => FileResource::make(
                    $order->getFirstMedia(Order::PICKUP_DRIVER_SIGNATURE_COLLECTION_NAME)
                ),

                Order::PICKUP_DRIVER_INSPECTION_BOL_COLLECTION_NAME => FileResource::make(
                    $order->getFirstMedia(Order::PICKUP_DRIVER_INSPECTION_BOL_COLLECTION_NAME)
                ),

                'delivery_customer_not_available' => $order->delivery_customer_not_available,
                'delivery_customer_refused_to_sign' => $order->delivery_customer_refused_to_sign,
                'delivery_customer_full_name' => $order->delivery_customer_full_name,
                'delivery_customer_signature' => FileResource::make(
                    $order->getFirstMedia(Order::DELIVERY_CUSTOMER_SIGNATURE_COLLECTION_NAME)
                ),
                'delivery_driver_signature' => FileResource::make(
                    $order->getFirstMedia(Order::DELIVERY_DRIVER_SIGNATURE_COLLECTION_NAME)
                ),

                Order::DELIVERY_DRIVER_INSPECTION_BOL_COLLECTION_NAME => FileResource::make(
                    $order->getFirstMedia(Order::DELIVERY_DRIVER_INSPECTION_BOL_COLLECTION_NAME)
                ),

                Order::ATTACHMENT_COLLECTION_NAME => $order->getMedia(Order::ATTACHMENT_COLLECTION_NAME)
                    ? FileResource::collection($order->getMedia(Order::ATTACHMENT_COLLECTION_NAME)->all())
                    : null,
                'documents' => $order->getMedia(Order::DRIVER_DOCUMENTS_COLLECTION_NAME)
                    ? FileResource::collection($order->getMedia(Order::DRIVER_DOCUMENTS_COLLECTION_NAME)->all())
                    : null,
                'photos' => !empty($photoResource) ? $photoResource : [],
                'driver_comments' => OrderCommentListResource::collection($order->comments),
                'public_token' => $order->public_token,
                'seen_by_driver' => $order->seen_by_driver,

                'allowed_status_change' => $order->getAllowedStatusChangeList(),
                'need_signature' => resolve(OrderService::class)->necessarySendSignatureLink($order, $request->user()),

                'payroll' => $order->payrolls->isNotEmpty() ? [
                    'id' => $order->payrolls[0]->id,
                    'start' => $order->payrolls[0]->start->timestamp,
                    'end' => $order->payrolls[0]->end->timestamp,
                    'is_salary_paid' => $order->payrolls[0]->is_paid,
                ] : null,

                'created_at' => $order->created_at->timestamp,
                'tags' => TagShortResource::collection($order->tags),
            ],
            $order->getAttribute('resource_info') ?? []
        );
    }
}
