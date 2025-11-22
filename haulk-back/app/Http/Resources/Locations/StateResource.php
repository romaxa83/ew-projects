<?php


namespace App\Http\Resources\Locations;


use App\Models\Locations\State;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StateResource extends JsonResource
{
    /**
     * @param Request $request
     * @return array
     *
     * @OA\Schema(
     *   schema="State",
     *   type="object",
     *           @OA\Property(
     *              property="data",
     *              type="object",
     *              description="State data",
     *              allOf={
     *                  @OA\Schema(
     *                          required={"id", "status", "name"},
     *                          @OA\Property(property="id", type="integer", description="State id", default="1"),
     *                          @OA\Property(property="status", type="boolean", description="State status"),
     *                          @OA\Property(property="name", type="string", description="State name"),
     *                          @OA\Property(property="short", type="string", description="State short name"),
     *                          @OA\Property(property="country_code", type="string", description="State country_code"),
     *                          @OA\Property(property="country_name", type="string", description="State country_name"),
     *                      )
     *           }
     *           ),
     * )
     *
     * @OA\Schema(
     *   schema="StateRaw",
     *   type="object",
     *              allOf={
     *                  @OA\Schema(
     *                          required={"id", "status", "name"},
     *                          @OA\Property(property="id", type="integer", description="State id", default="1"),
     *                          @OA\Property(property="status", type="boolean", description="State status"),
     *                          @OA\Property(property="name", type="string", description="State name"),
     *                          @OA\Property(property="short", type="string", description="State short name"),
     *                          @OA\Property(property="country_code", type="string", description="State country_code"),
     *                          @OA\Property(property="country_name", type="string", description="State country_name"),
     *                      )
     *           }
     * )
     *
     * @OA\Schema(
     *   schema="StatePaginate",
     *   @OA\Property(
     *      property="data",
     *      description="State paginated list",
     *      type="array",
     *      @OA\Items(ref="#/components/schemas/StateRaw")
     *   ),
     *   @OA\Property(
     *      property="links",
     *      ref="#/components/schemas/PaginationLinks",
     *   ),
     *   @OA\Property(
     *      property="meta",
     *      ref="#/components/schemas/PaginationMeta",
     *   ),
     * )
     *
     * @OA\Schema(
     *   schema="StateList",
     *   @OA\Property(
     *      property="data",
     *      description="State list",
     *      type="array",
     *      @OA\Items(ref="#/components/schemas/StateRaw")
     *   ),
     * )
     */
    public function toArray($request)
    {
        /** @var State $state */
        $state = $this;
        return [
            'id' => $state->id,
            'status' => $state->status,
            'name' => $state->name,
            'short' => $state->state_short_name,
            'country_code' => $state->country_code,
            'country_name' => $state->country_name,
        ];
    }
}
