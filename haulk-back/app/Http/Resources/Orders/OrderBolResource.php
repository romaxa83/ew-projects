<?php

namespace App\Http\Resources\Orders;

use App\Http\Resources\Contacts\ContactResource;
use App\Models\Contacts\Contact;
use App\Models\Orders\Order;
use App\Models\Orders\OrderSignature;
use App\Services\Orders\OrderService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderBolResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     *
     * @OA\Schema(
     *    schema="OrderBolResource",
     *    type="object",
     *        @OA\Property(
     *            property="data",
     *            type="object",
     *            description="Order data",
     *            allOf={
     *            @OA\Schema(
     *                @OA\Property(property="load_id", type="string", description="Order load id"),
     *                @OA\Property(property="state", type="string", description="Order state (new, assigned, pickedup, delivered, billed, paid, deleted)"),
     *                @OA\Property(property="pickup_contact", type="object", description="Order pickup contact", allOf={@OA\Schema(ref="#/components/schemas/ContactResourceRaw")}),
     *                @OA\Property(property="pickup_date_actual", type="integer", description="Actual order pickup date timestamp"),
     *                @OA\Property(property="delivery_contact", type="object", description="Order delivery contact", allOf={@OA\Schema(ref="#/components/schemas/ContactResourceRaw")}),
     *                @OA\Property(property="delivery_date_actual", type="integer", description="Actual order delivery date timestamp"),
     *                @OA\Property(property="vehicles", type="array", description="Order vehicles", @OA\Items(ref="#/components/schemas/VehicleResourceRaw")),
     *                @OA\Property(property="need_signature", type="string", description="Type signature (if need)", enum={"pickup","delivery"}, nullable="true"),
     *            )
     *            }
     *        ),
     * )
     *
     */
    public function toArray($request): array
    {
        /**@var Order $order*/
        /**@var OrderSignature $signature*/

        $signatureType = null;

        if ($this->resource instanceof OrderSignature) {
            $order = $this->resource->order;

            $signatureType = resolve(OrderService::class)->getSignatureType($this->resource);
        } else {
            $order = $this->resource;
        }

        $order->loadMissing(
            [
                'vehicles.pickupInspection.media',
                'vehicles.deliveryInspection.media'
            ]
        );


        $pickup_contact = new Contact($order->pickup_contact ?: []);
        $delivery_contact = new Contact($order->delivery_contact ?: []);


        return [
            'load_id' => $order->load_id,
            'state' => $order->getStateForCrm(),
            'pickup_contact' => new ContactResource($pickup_contact),
            'pickup_date_actual' => $order->pickup_date_actual ? (int)$order->pickup_date_actual : null,
            'delivery_contact' => new ContactResource($delivery_contact),
            'delivery_date_actual' => $order->delivery_date_actual ? (int)$order->delivery_date_actual : null,
            'vehicles' => VehicleListResource::collection($order->vehicles),
            'need_signature' => $signatureType
        ];
    }


}
