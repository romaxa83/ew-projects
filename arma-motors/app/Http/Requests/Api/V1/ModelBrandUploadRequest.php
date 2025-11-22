<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(type="object", title="ModelBrandUploadRequest",
 *     @OA\Property(property="ModelId", title="Model ID", example="97a920c1-b840-11e9-832e-000c29f1d0a8",
 *         description="Uuid модели авто"
 *     ),
 *     @OA\Property(property="ModelName", title="Model Name", example="Rav 4",
 *         description="Название модели авто"
 *     ),
 *     @OA\Property(property="BrandId", title="Brand ID", example="97a920c1-b840-11e9-832e-000c29f1d0a4",
 *         description="Uuid бренда авто"
 *     ),
 *      @OA\Property(property="BrandName", title="Brand Name", example="Toyota",
 *         description="Название бренда авто"
 *     ),
 *
 * )
 */
class ModelBrandUploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            '*.modelId'  => ['required', 'string'],
            '*.modelName'  => ['required', 'string'],
            '*.brandId'  => ['required', 'string'],
            '*.brandName'  => ['required', 'string'],
        ];
    }
}
