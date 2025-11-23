<?php

namespace WezomCms\SmsVerify\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     type="object",
 *     title="Request for check sms code",
 *     required={"smsToken", "code"}
 * )
 */
class SmsCheckRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'smsToken' => ['required', 'string'],
            'code' => ['required', 'string'],
        ];
    }

    public function attributes(): array
    {
        return [];
    }

    /**
     * @OA\Property(property="smsToken", title="Sms Token", description="Токен выданый при запросе на верификацию", example="e20c13f6-0e37-47bc-874a-f6d3c4812db3")
     * @OA\Property(property="code", title="Code", description="Sms кода, пришедший на телефон", example="9545")
     */
}
