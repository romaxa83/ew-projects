<?php

namespace App\Http\Resources\History;

use App\Foundations\Modules\History\Models\History;
use App\Models\Settings\Settings;
use Carbon\CarbonImmutable;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(schema="HistoryItemResourse", type="object",
 *     @OA\Property(property="field_name", type="object", description="Field name", allOf={
 *         @OA\Schema(
 *             @OA\Property(property="new", type="string", description="New field value"),
 *             @OA\Property(property="old", type="string", description="Old field value"),
 *             @OA\Property(property="type", type="string", description="Action type"),
 *         )}
 *     ),
 * ),
 * @OA\Schema(schema="UserHistory", type="object", allOf={
 *     @OA\Schema(
 *         required={"full_name", "first_name", "last_name", "email"},
 *         @OA\Property(property="full_name", type="string", description="User full name"),
 *         @OA\Property(property="first_name", type="string", description="User first name"),
 *         @OA\Property(property="last_name", type="string", description="User last name"),
 *         @OA\Property(property="email", type="string", description="email"),
 *     )}
 * ),
 * @OA\Schema(schema="HistoryDetailedResource", type="object", allOf={
 *     @OA\Schema(
 *         @OA\Property(property="id", type="integer", description="Record id"),
 *         @OA\Property(property="model_id", type="integer", description="Model  id"),
 *         @OA\Property(property="user_id", type="string", description="User id"),
 *         @OA\Property(property="message", type="string", description="message"),
 *         @OA\Property(property="histories", description="Histories (has a list of properties with name of changed field)", type="object", allOf={
 *             @OA\Schema(ref="#/components/schemas/HistoryItemResourse")
 *         }),
 *         @OA\Property(property="meta", type="array", description="meta",
 *             @OA\Items(type="object", allOf={
 *                 @OA\Schema(
 *                     @OA\Property(property="full_name", type="string", description="full_name"),
 *                     @OA\Property(property="load_id", type="string", description="load_id"),
 *                     @OA\Property(property="user_id", type="integer", description="user_id"),
 *                 )}
 *             ),
 *         ),
 *         @OA\Property(property="user", type="object", description="user", allOf={
 *             @OA\Schema(ref="#/components/schemas/UserHistory")
 *         }),
 *         @OA\Property(property="performed_at", type="integer", description="Record timestamp"),
 *         @OA\Property(property="performed_timezone", type="integer", description="Record timezone"),
 *    )}
 * )
 *
 * @OA\Schema(schema="HistoryPaginatedResource",
 *     @OA\Property(property="data", description="History detailed paginated list", type="array",
 *         @OA\Items(ref="#/components/schemas/HistoryDetailedResource")
 *     ),
 *     @OA\Property(property="links", ref="#/components/schemas/PaginationLinks",),
 *     @OA\Property(property="meta", ref="#/components/schemas/PaginationMeta",),
 * )
 *
 * @mixin History
 */

class HistoryPaginatedResource extends JsonResource
{
    public function toArray($request)
    {
        $historiesArray = $this->linkRelation($this->details ? : [], $request);
        return [
            'id' => $this->id,
            'model_id' => $this->model_id,
            'user_id' => $this->user_id,
            'meta' => $this->msg_attr,
            'user' => [
                'full_name' => $this->initiator?->full_name,
                'first_name' => $this->initiator?->first_name,
                'last_name' => $this->initiator?->last_name,
                'email' => $this->initiator?->email->getValue(),
            ],
            'message' => __($this->msg, $this->msg_attr),
            'histories' => $historiesArray,
            'performed_at' => (new CarbonImmutable($this->performed_at))->timestamp,
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
