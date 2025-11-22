<?php


namespace App\Http\Resources\Locations;


use App\Models\Locations\City;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CityResource extends JsonResource
{
    /**
     * @param Request $request
     * @return array
     *
     * @OA\Schema(
     *   schema="City",
     *   type="object",
     *           @OA\Property(
     *              property="data",
     *              type="object",
     *              description="City data",
     *              allOf={
     *                      @OA\Schema(
     *                          required={"id", "status", "name","state_id","zip"},
     *                          @OA\Property(property="id", type="integer", description="City id"),
     *                          @OA\Property(property="status", type="boolean", description="City status"),
     *                          @OA\Property(property="name", type="string", description="City name"),
     *                          @OA\Property(property="state_id", type="integer", description="State id"),
     *                          @OA\Property(property="state_name", type="string", description="State name"),
     *                          @OA\Property(property="state_short", type="string", description="State short name"),
     *                          @OA\Property(property="zip", type="string", description="City zip"),
     *                          @OA\Property(property="country_code", type="string", description="City country_code"),
     *                          @OA\Property(property="country_name", type="string", description="City country_name"),
     *                      )
     *           }
     *           ),
     * )
     *
     * @OA\Schema(
     *   schema="CityRaw",
     *   type="object",
     *              allOf={
     *                      @OA\Schema(
     *                          required={"id", "status", "name","state_id","zip"},
     *                          @OA\Property(property="id", type="integer", description="City id"),
     *                          @OA\Property(property="status", type="boolean", description="City status"),
     *                          @OA\Property(property="name", type="string", description="City name"),
     *                          @OA\Property(property="state_id", type="integer", description="State id"),
     *                          @OA\Property(property="state_name", type="string", description="State name"),
     *                          @OA\Property(property="state_short", type="string", description="State short name"),
     *                          @OA\Property(property="zip", type="string", description="City zip"),
     *                          @OA\Property(property="country_code", type="string", description="City country_code"),
     *                          @OA\Property(property="country_name", type="string", description="City country_name"),
     *                      )
     *           }
     * )
     *
     * @OA\Schema(
     *   schema="CityPaginate",
     *   @OA\Property(
     *      property="data",
     *      description="City paginated list",
     *      type="array",
     *      @OA\Items(ref="#/components/schemas/CityRaw")
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
     */
    public function toArray($request)
    {
        /** @var City $city */
        $city = $this;
        return [
            'id' => $city->id,
            'status' => $city->status,
            'name' => $city->name,
            'zip' => $city->zip,
            'state_id' => $city->state_id,
            'state_name' => $city->state ? $city->state->name : null,
            'state_short' => $city->state ? $city->state->state_short_name : null,
            'country_code' => $city->country_code,
            'country_name' => $city->country_name,
        ];
    }
}
