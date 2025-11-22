<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuthResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request $request
     * @return array
     *
     * @OA\Schema(
     *   schema="AuthDataResource",
     *   type="object",
     *              @OA\Property(
     *              property="data",
     *              type="object",
     *              description="City data",
     *              allOf={
     *                  @OA\Schema(
     *                          required={"token_type", "expires_in", "access_token", "refresh_token"},
     *                          @OA\Property(property="token_type", type="string", description="Token type", default="Bearer"),
     *                          @OA\Property(property="expires_in", type="string", description="Token lifetime in seconds"),
     *                          @OA\Property(property="expires_at", type="string", description="Datetime for token will be expires"),
     *                          @OA\Property(property="access_token", type="string", description="User access token"),
     *                          @OA\Property(property="refresh_token", type="string", description="User refresh token"),
     *                      )
     *           }
     *     ),
     * )
     */
    public function toArray($request)
    {
        return $this->resource;
    }


    public function withResponse($request, $response)
    {
        $response->header('Content-Type', 'application/json');
    }
}
