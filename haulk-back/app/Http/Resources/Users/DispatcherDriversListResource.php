<?php


namespace App\Http\Resources\Users;


use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DispatcherDriversListResource extends JsonResource
{
    /**
     * @param Request $request
     * @return array
     *
     * @OA\Schema(schema="DispatcherDriversListResource", type="object", allOf={
     *      @OA\Schema(required={"id", "full_name", "first_name", "last_name", "email","status","security_level"},
     *          @OA\Property(property="id", type="integer", description="User id"),
     *          @OA\Property(property="full_name", type="string", description="User full name"),
     *          @OA\Property(property="first_name", type="string", description="User first name"),
     *          @OA\Property(property="last_name", type="string", description="User last name"),
     *          @OA\Property(property="status", type="integer", description="User status"),
     *      )
     * })
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'full_name' => $this->full_name,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'status' => $this->status,
        ];
    }
}
