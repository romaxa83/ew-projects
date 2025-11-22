<?php

namespace App\Http\Resources\BodyShop\History;

use App\Models\BodyShop\Settings\Settings;
use App\Models\History\History;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin History
 */
class HistoryListResource extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     *
     * @OA\Schema(
     *    schema="HistoryResourceBS",
     *    type="object",
     *    allOf={
     *        @OA\Schema(
     *            @OA\Property(property="id", type="integer", description="Record id"),
     *            @OA\Property(property="model_id", type="integer", description="Model  id"),
     *            @OA\Property(property="user_id", type="integer", description="User id"),
     *            @OA\Property(property="message", type="string", description="message"),
     *            @OA\Property(property="meta", type="array", description="meta", @OA\Items(
     *                  type="object",
     *                  allOf={
     *                      @OA\Schema(
     *                          @OA\Property(property="full_name", type="string", description="full_name"),
     *                          @OA\Property(property="load_id", type="string", description="load_id"),
     *                          @OA\Property(property="user_id", type="integer", description="user_id"),
     *                      )
     *                  }
     *              ),
     *            ),
     *            @OA\Property(property="performed_at", type="integer", description="Record timestamp"),
     *            @OA\Property(property="performed_timezone", type="integer", description="Record timezone"),
     *        )
     *    }
     * )
     *
     * @OA\Schema(
     *    schema="HistoryListResourceBS",
     *    @OA\Property(
     *        property="data",
     *        description="History list",
     *        type="array",
     *        @OA\Items(ref="#/components/schemas/HistoryResourceBS")
     *    ),
     * )
     *
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'model_id' => $this->model_id,
            'user_id' => $this->user_id,
            'message' => $this->message,
            'meta' => $this->meta,
            'performed_at' => $this->performed_at->timestamp,
            'performed_timezone' => Settings::getParam('timezone'),
        ];
    }
}
