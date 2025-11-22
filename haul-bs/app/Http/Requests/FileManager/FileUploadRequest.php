<?php

namespace App\Http\Requests\FileManager;

use App\Dto\FileBrowser\FileUploadDto;
use App\Foundations\Modules\Permission\Models\Role;
use App\Models\Users\User;
use App\Validators\FileBrowser\PathValidator;
use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpFoundation\FileBag;

/**
 * @property string action
 * @property string source
 * @property string|null path
 * @property array|null|FileBag files
 * @property string|null url
 */
class FileUploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
//            'action' => ['required', 'string'],
            'path' => ['nullable', 'string', new PathValidator()],
            'source' => ['required', 'string'],
            'files' => ['sometimes', 'array'],
            'files.*' => ['sometimes', 'file'],
            'url' => ['sometimes', 'url'],
        ];
    }

    public function getDto(): FileUploadDto
    {
        $fileBrowserPrefix = null;

        if ($user = $this->user(Role::GUARD_USER)) {
            $fileBrowserPrefix = $user->getFileBrowserPrefix();
        }

        return FileUploadDto::byParams(
            $this->source,
            $this->path,
            $this->files->get('files'),
            $this->url,
            $fileBrowserPrefix
        );
    }
}

