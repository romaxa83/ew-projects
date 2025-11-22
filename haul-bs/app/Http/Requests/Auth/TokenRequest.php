<?php

namespace App\Http\Requests\Auth;

use App\Foundations\Http\Requests\BaseFormRequest;

/**
 * @OA\Schema(type="object", title="TokenRequest",
 *     required={"token"},
 *     @OA\Property(property="token", type="string", description="Token",
 *         example="def5020088df2ca413818352cf5bc074eb6a83db523da9393f6a551f4d056e81220aa2c1a9b6d06"
 *     ),
 * )
 */

class TokenRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'token' => ['required', 'string'],
        ];
    }
}

