<?php


namespace App\Http\Resources\BodyShop\Roles;


use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Spatie\Permission\Models\Role;

class RoleResource extends JsonResource
{
    /**
     * @param Request $request
     * @return array
     *
     * @OA\Schema(
     *   schema="RoleRawBS",
     *   type="object",
     *              allOf={
     *                  @OA\Schema(
     *                          required={"id", "name", "title"},
     *                          @OA\Property(property="id", type="integer", description="Role id"),
     *                          @OA\Property(property="name", type="string", description="Role name"),
     *                      )
     *           }
     * )
     *
     * @OA\Schema(
     *   schema="RolesListBS",
     *   @OA\Property(
     *      property="data",
     *      description="Role list",
     *      type="array",
     *      @OA\Items(ref="#/components/schemas/RoleRawBS")
     *   ),
     * )
     */
    public function toArray($request)
    {
        /** @var Role $role */
        $role = $this;
        return [
            'id' => $role->id,
            'name' => trans('body-shop.' . $role->name),
        ];
    }
}
