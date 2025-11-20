<?php

namespace App\Http\Request\Import;

use App\Rules\Import\ExtensionRule;
use App\Rules\Import\FileIosLinkRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(type="object", title="IosLinkImport Request",
 *     @OA\Property(property="file", type="file",
 *          description="Файл с ссылками в формате xlsx , xls"
 *     ),
 *     required={"file"},
 * )
 */

class IosLinkImportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', new ExtensionRule(), new FileIosLinkRule()],
        ];
    }
}
