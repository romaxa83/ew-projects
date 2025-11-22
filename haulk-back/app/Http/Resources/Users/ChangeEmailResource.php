<?php

namespace App\Http\Resources\Users;

use Illuminate\Http\Resources\Json\JsonResource;

class ChangeEmailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     *
     * @OA\Schema(
     *    schema="ChangeEmailResourceRaw",
     *    type="object",
     *        allOf={
     *            @OA\Schema(
     *                    required={"id", "user", "new_email", "new_email_confirmed"},
     *                        @OA\Property(property="id", type="integer", description=""),
     *                        @OA\Property(property="user", type="object", description="", allOf={
     *                            @OA\Schema(
     *                                @OA\Property(property="id", type="integer", description=""),
     *                                @OA\Property(property="role_id", type="integer", description=""),
     *                                @OA\Property(property="full_name", type="string", description=""),
     *                                @OA\Property(property="email", type="string", description=""),
     *                                @OA\Property(property="phone", type="string", description=""),
     *                                @OA\Property(property="phone_extension", type="string", description=""),
     *                            )
     *                        }),
     *                        @OA\Property(property="new_email", type="string", description=""),
     *                        @OA\Property(property="new_email_confirmed", type="boolean", description=""),
     *              )
     *        }
     * )
     *
     * @OA\Schema(
     *    schema="ChangeEmailResource",
     *    type="object",
     *        @OA\Property(
     *            property="data",
     *            type="object",
     *            description="ChangeEmail request data",
     *            allOf={
     *                @OA\Schema(
     *                    required={"id", "user", "new_email", "new_email_confirmed"},
     *                        @OA\Property(property="id", type="integer", description=""),
     *                        @OA\Property(property="user", type="object", description="", allOf={
     *                            @OA\Schema(
     *                                @OA\Property(property="id", type="integer", description=""),
     *                                @OA\Property(property="role_id", type="integer", description=""),
     *                                @OA\Property(property="full_name", type="string", description="User full name"),
     *                                @OA\Property(property="first_name", type="string", description="User first name"),
     *                                @OA\Property(property="last_name", type="string", description="User last name"),
     *                                @OA\Property(property="email", type="string", description=""),
     *                                @OA\Property(property="phone", type="string", description=""),
     *                                @OA\Property(property="phone_extension", type="string", description=""),
     *                            )
     *                        }),
     *                        @OA\Property(property="new_email", type="string", description=""),
     *                        @OA\Property(property="new_email_confirmed", type="boolean", description=""),
     *                )
     *            }
     *        ),
     * )
     *
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'user' => [
                'id' => $this->user->id,
                'role_id' => $this->user->roles->first()->id,
                'full_name' => $this->user->full_name,
                'first_name' => $this->user->first_name,
                'last_name' => $this->user->last_name,
                'email' => $this->user->email,
                'phone' => $this->user->phone,
                'phone_extension' => $this->user->phone_extension,
            ],
            'new_email' => $this->new_email,
            'new_email_confirmed' => (boolean) $this->new_email_confirmed,
        ];
    }
}
