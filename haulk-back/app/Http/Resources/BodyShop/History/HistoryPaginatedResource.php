<?php

namespace App\Http\Resources\BodyShop\History;

use App\Http\Resources\Users\UserHistoryResource;
use App\Models\BodyShop\Settings\Settings;
use App\Models\History\History;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin History
 */
class HistoryPaginatedResource extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     *
     * @OA\Schema(schema="HistoryItemBSResourse", type="object",
     *     @OA\Property(property="field_name", type="object", description="Field name", allOf={
     *          @OA\Schema(
     *              @OA\Property(property="new", type="string", description="New field value"),
     *              @OA\Property(property="old", type="string", description="Old field value"),
     *              @OA\Property(property="type", type="string", description="Action type"),
     *          )
     *       }
     *    ),
     * ),
     * @OA\Schema(
     *    schema="HistoryDetailedResourceBS",
     *    type="object",
     *    allOf={
     *        @OA\Schema(
     *            @OA\Property(property="id", type="integer", description="Record id"),
     *            @OA\Property(property="model_id", type="integer", description="Model  id"),
     *            @OA\Property(property="user_id", type="string", description="User id"),
     *            @OA\Property(property="message", type="string", description="message"),
     *            @OA\Property(property="histories", description="Histories (has a list of properties with name of changed field)", type="object", allOf={
     *                @OA\Schema(ref="#/components/schemas/HistoryItemBSResourse")
     *            }),
     *           @OA\Property(property="meta", type="array", description="meta", @OA\Items(
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
     *          @OA\Property(property="user", type="object", description="user", allOf={
     *              @OA\Schema(ref="#/components/schemas/UserHistory")
     *          }),
     *            @OA\Property(property="performed_at", type="integer", description="Record timestamp"),
     *            @OA\Property(property="performed_timezone", type="integer", description="Record timezone"),
     *        )
     *    }
     * )
     *
     * @OA\Schema(
     *    schema="HistoryPaginatedResourceBS",
     *    @OA\Property(
     *        property="data",
     *        description="History detailed paginated list",
     *        type="array",
     *        @OA\Items(ref="#/components/schemas/HistoryDetailedResourceBS")
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
        $historiesArray = $this->linkRelation($this->histories ? : [], $request);
        return [
            'id' => $this->id,
            'model_id' => $this->model_id,
            'user_id' => $this->user_id,
            'meta' => $this->meta,
            'user' => UserHistoryResource::make($this->user),
            'message' => $this->message,
            'histories' => $historiesArray,
            'performed_at' => $this->performed_at->timestamp,
            'performed_timezone' => Settings::getParam('timezone'),
        ];
    }


    private function linkRelation($histories, $request): array
    {
        $historiesArray = [];
        foreach ($histories as $key => $history) {
            $historiesArray[$key] = $history;
        }
        return $historiesArray;
    }
}
