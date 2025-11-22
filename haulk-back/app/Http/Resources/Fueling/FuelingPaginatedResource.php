<?php

namespace App\Http\Resources\Fueling;

use App\Http\Resources\Users\UserMiniResource;
use App\Models\Fueling\FuelCard;
use App\Models\Fueling\Fueling;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Fueling
 */
class FuelingPaginatedResource extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     *
     * @OA\Schema(
     *    schema="FuelingResource",
     *    type="object",
     *    allOf={
     *        @OA\Schema(
     *            @OA\Property(property="id", type="integer", description="Record id"),
     *            @OA\Property(property="card", type="string", description="card"),
     *            @OA\Property(property="transaction_date", type="string", description="transaction_date"),
     *            @OA\Property(property="timezone", type="string", description="timezone"),
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
     *        )
     *    }
     * )
     *
     * @OA\Schema(
     *    schema="FuelingPaginatedResource",
     *    @OA\Property(
     *        property="data",
     *        description="FuelCard detailed paginated list",
     *        type="array",
     *        @OA\Items(ref="#/components/schemas/FuelingResource")
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
        return [
            'id' => $this->id,
            'card' => $this->card,
            'transaction_date' => (string) (!isset($messages['transaction_date'][0]) ? Carbon::parse($this->transaction_date)->timestamp : $this->transaction_date),
            'timezone' => $this->timezone,
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
        ];
    }
}
