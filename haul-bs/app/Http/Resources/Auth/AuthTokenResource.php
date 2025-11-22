<?php

namespace App\Http\Resources\Auth;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="AuthTokenResource",
 *     type="object",
 *     allOf={@OA\Schema(
 *          required={"token_type", "expires_in", "access_token", "refresh_token"},
 *          @OA\Property(property="token_type", type="string", example="Bearer"),
 *          @OA\Property(property="expires_in", type="integer", example="31622400"),
 *          @OA\Property(property="access_token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIzIiwianRpIjoiZmRmMmEzM2Q3MTUzN2QyNTMwZTI2NzAyZWM1N2Y2ZWNlOWZjNGRiZjc0"),
 *          @OA\Property(property="refresh_token", type="string", example="def502002001521d7dc7abfa3cfda753650a849a9e2063219d518828c4dca559d4a34d939a0242d033e3b684c00f7667b17646b37bff7a73502d"),
 *         )
 *     }
 * )
 */
class AuthTokenResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'token_type' => $this['token_type'],
            'expires_in' => $this['expires_in'],
            'access_token' => $this['access_token'],
            'refresh_token' => $this['refresh_token'],
        ];
    }
}
