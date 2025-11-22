<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(type="object", title="Refresh token Request",
 *     required={"refresh_token"},
 *     @OA\Property(property="refresh_token", type="string", description="Refresh token",
 *         example="def5020088df2ca413818352cf5bc074eb6a83db523da9393f6a551f4d056e81220aa2c1a9b6d06"
 *     ),
 * )
 */

class RefreshTokenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'refresh_token' => ['required', 'string'],
        ];
    }
}
