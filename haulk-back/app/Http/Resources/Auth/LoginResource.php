<?php

namespace App\Http\Resources\Auth;

use App\Models\Users\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LoginResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request $request
     * @return array
     *
     * @OA\Schema(
     *     schema="LoginResource",
     *     type="object",
     *     @OA\Property(
     *         property="data",
     *         type="object",
     *         allOf={
     *             @OA\Schema(
     *                 @OA\Property(property="redirect_url", type="string",),
     *                 @OA\Property(property="token", type="string",),
     *             )
     *         }
     *     ),
     * )
     */
    public function toArray($request)
    {
        $isBodyShopUser = !empty($this['user'])
            && $this['user'] instanceof User
            && $this['user']->isBodyShopUser();

        return [
            'redirect_url' => $isBodyShopUser ? config('frontend.bodyshop_url') : config('frontend.url'),
            'token' => $this['refresh_token'],
            'is_body_shop_user' => $isBodyShopUser,
        ];
    }


    public function withResponse($request, $response)
    {
        $response->header('Content-Type', 'application/json');
    }
}
