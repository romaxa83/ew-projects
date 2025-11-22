<?php

namespace App\Http\Resources\Fueling;

use App\Http\Resources\Users\UserMiniResource;
use App\Models\Fueling\Fueling;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use Validator;

/**
 * @mixin Fueling
 */
class FuelingValidatedResource extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     *
     * @OA\Schema(
     *    schema="FuelingValidatedResource",
     *    type="object",
     *    allOf={
     *        @OA\Schema(
     *            @OA\Property(property="id", type="integer", description="Record id"),
     *            @OA\Property(property="card", type="string", description="card"),
     *            @OA\Property(property="transaction_date", type="string", description="transaction_date"),
     *            @OA\Property(property="user", type="string", description="user"),
     *            @OA\Property(property="location", type="string", description="location"),
     *            @OA\Property(property="state", type="string", description="state"),
     *            @OA\Property(property="fees", type="string", description="fees"),
     *            @OA\Property(property="item", type="string", description="item"),
     *            @OA\Property(property="unit_price", type="string", description="unit_price"),
     *            @OA\Property(property="quantity", type="string", description="quantity"),
     *            @OA\Property(property="amount", type="string", description="amount"),
     *            @OA\Property(
     *              property="status",
     *              type="string",
     *              description="status (paid|due)",
     *              enum={"paid", "due"}
     *            ),
     *            @OA\Property(
     *               property="source",
     *               type="string",
     *               description="status (manually|import)",
     *               enum={"manually", "import"}
     *            ),
     *            @OA\Property(
     *                property="provider",
     *                type="string",
     *                description="status (quikq|efs)",
     *                enum={"quikq", "efs"}
     *            ),
     *            @OA\Property(property="valid", type="boolean", description="valid"),
     *            @OA\Property(property="driver", type="object", description="driver", allOf={
     *              @OA\Schema(ref="#/components/schemas/UserMini")
     *            }),
     *            @OA\Property(property="fuelCard", type="object", description="fuelCard", allOf={
     *              @OA\Schema(ref="#/components/schemas/FuelCardShortResource")
     *            }),
     *            @OA\Property(property="created_at", type="integer", description="timestamp"),
     *            @OA\Property(property="updated_at", type="integer", description="timestamp"),
     *            @OA\Property(property="card_validate_message", type="string", description="message"),
     *            @OA\Property(property="transaction_date_validate_message", type="string", description="message"),
     *            @OA\Property(property="user_validate_message", type="string", description="message"),
     *            @OA\Property(property="location_validate_message", type="string", description="message"),
     *            @OA\Property(property="state_validate_message", type="string", description="message"),
     *            @OA\Property(property="fees_validate_message", type="string", description="message"),
     *            @OA\Property(property="item_validate_message", type="string", description="message"),
     *            @OA\Property(property="unit_price_validate_message", type="string", description="message"),
     *            @OA\Property(property="quantity_validate_message", type="string", description="message"),
     *            @OA\Property(property="amount_validate_message", type="string", description="message"),
     *        )
     *    }
     * )
     *
     * @OA\Schema(
     *    schema="FuelingValidatedPaginatedResource",
     *    @OA\Property(
     *        property="data",
     *        description="FuelCard detailed paginated list",
     *        type="array",
     *        @OA\Items(ref="#/components/schemas/FuelingValidatedResource")
     *    ),
     *    @OA\Property(
     *        property="links",
     *        ref="#/components/schemas/PaginationLinks",
     *    ),
     *    @OA\Property(
     *        property="meta",
     *        ref="#/components/schemas/PaginationMeta",
     *    ),
     * )
     *
     */
    public function toArray($request)
    {
        $messages = $this->validStatus->messages();

        return [
            'id' => $this->id,
            'card' => $this->card,
            'transaction_date' => (string) (!isset($messages['transaction_date'][0]) ? Carbon::parse($this->transaction_date)->timestamp : $this->transaction_date),
            'timezone' => $this->time,
            'user' => $this->user,
            'location' => $this->location,
            'state' => $this->state,
            'fees' => $this->fees,
            'item' => $this->item,
            'unit_price' => $this->unit_price,
            'quantity' => $this->quantity,
            'amount' => $this->amount,
            'status' => $this->status,
            'source' => $this->source,
            'provider' => $this->provider,
            'valid' => $this->valid,
            'driver' => $this->driver ? UserMiniResource::make($this->driver) : null,
            'fuelCard' => $this->fuelCard ? FuelCardShortResource::make($this->fuelCard) : null,
            'created_at' => $this->created_at->timestamp ?? null,
            'updated_at' => $this->updated_at->timestamp ?? null,

            'card_validate_message' => $messages['card'][0] ?? null,
            'transaction_date_validate_message' => $messages['transaction_date'][0] ?? null,
            'user_validate_message' => $messages['user'][0] ?? null,
            'location_validate_message' => $messages['location'][0] ?? null,
            'state_validate_message' => $messages['state'][0] ?? null,
            'fees_validate_message' => $messages['fees'][0] ?? null,
            'item_validate_message' => $messages['item'][0] ?? null,
            'unit_price_validate_message' => $messages['unit_price'][0] ?? null,
            'quantity_validate_message' => $messages['quantity'][0] ?? null,
            'amount_validate_message' => $messages['amount'][0] ?? null,
        ];
    }
}
