<?php


namespace App\Http\Resources;


use App\Models\Users\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChangeLanguageResource extends JsonResource
{
    /**
     * @param Request $request
     * @return array
     *
     * @OA\Schema(
     *   schema="ChangeLanguage",
     *   type="object",
     *           @OA\Property(
     *              property="data",
     *              type="object",
     *              description="User data",
     *              allOf={
     *                      @OA\Schema(
     *                          required={"id", "name", "language"},
     *                          @OA\Property(property="id", type="integer", description="User id"),
     *                          @OA\Property(property="role_id", type="integer", description="User role id"),
     *                          @OA\Property(property="language", type="string", description="Language slug"),
     *                      )
     *           }
     *           ),
     * )
     */
    public function toArray($request)
    {
        /** @var User $user */
        $user = $this;
        return [
            'id' => $user->id,
            'role_id' => $user->roles->first()->id,
            'language' => $user->language,
        ];
    }
}