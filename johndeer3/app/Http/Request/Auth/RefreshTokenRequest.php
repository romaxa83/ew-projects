<?php

namespace App\Http\Request\Auth;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(type="object", title="Refresh token Request",
 *     @OA\Property(property="refresh_token", type="string", description="Refresh token",
 *         example="def5020088df2ca413818352cf5bc074eb6a83db523da9393f6a551f4d056e81220aa2c1a9b6d06fcdaaf066fbaea1540043e017ed9a12cf8e79be0e14790d7ec909e493adc0cf324b1b590df87fa107aedff6a5a6987080c8a70f5e7582c727e8b5d43b53f8246c261e9bc57954ee7b833e5c22f57"
 *     ),
 *     required={"refresh_token"}
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
