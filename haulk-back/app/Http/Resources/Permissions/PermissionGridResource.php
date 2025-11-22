<?php


namespace App\Http\Resources\Permissions;


use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PermissionGridResource extends JsonResource
{
    /**
     * @param Request $request
     * @return array
     *
     * @OA\Schema(
     *   schema="PermissionGrid",
     *   type="object",
     *              @OA\Property(
     *              property="data",
     *              type="object",
     *              description="Permission grid data",
     *              allOf={
     *                  @OA\Schema(
     *                          required={"moduleName"},
     *                          @OA\Property(property="moduleName", type="object", description="Module name",
     *                              allOf={
     *                                  @OA\Schema(
     *                                      required={"moduleName"},
     *                                      @OA\Property(property="actionName", type="boolean", description="Action Name true/flase"),
     *                                  )
     *                              }
     *                          ),
     *                  )
     *              }
     *           ),
     * )
     *
     * @OA\Schema(
     *   schema="PermissionGridRaw",
     *   type="object",
     *              allOf={
     *                  @OA\Schema(
     *                          required={"moduleName"},
     *                          @OA\Property(property="moduleName", type="object", description="Module name",
     *                              allOf={
     *                                  @OA\Schema(
     *                                      required={"moduleName"},
     *                                      @OA\Property(property="actionName", type="boolean", description="Action Name true/flase"),
     *                                  )
     *                              }
     *                          ),
     *                  )
     *              }
     * )
     */
    public function toArray($request)
    {
        return parent::toArray($request);
    }
}