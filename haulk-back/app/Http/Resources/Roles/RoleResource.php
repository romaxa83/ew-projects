<?php


namespace App\Http\Resources\Roles;


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
     *   schema="RoleRaw",
     *   type="object",
     *              allOf={
     *                  @OA\Schema(
     *                          required={"id", "name"},
     *                          @OA\Property(property="id", type="integer", description="Role id"),
     *                          @OA\Property(property="name", type="string", description="Role name"),
     *                      )
     *           }
     * )
     *
     * @OA\Schema(
     *   schema="Role",
     *   type="object",
     *            @OA\Property(
     *              property="data",
     *              type="object",
     *              description="Role data",
     *              allOf={
     *                  @OA\Schema(
     *                          required={"id", "name"},
     *                          @OA\Property(property="id", type="integer", description="Role id"),
     *                          @OA\Property(property="name", type="string", description="Role name"),
     *                      )
     *              }
     *           ),
     * )
     * @OA\Schema(
     *   schema="RolesList",
     *   @OA\Property(
     *      property="data",
     *      description="Role list",
     *      type="array",
     *      @OA\Items(ref="#/components/schemas/RoleRaw")
     *   ),
     * )
     */
    public function toArray($request)
    {
        /** @var Role $role */
        $role = $this;
        return [
            'id' => $role->id,
            'name' => $role->name,
        ];
    }
}