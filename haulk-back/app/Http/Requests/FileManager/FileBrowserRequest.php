<?php

namespace App\Http\Requests\FileManager;

use App\Dto\FileBrowser\FileBrowserDto;
use App\Services\FileBrowser\Actions\FolderCreate;
use App\Services\FileBrowser\Actions\FolderRemove;
use App\Services\FileBrowser\Actions\FolderRename;
use App\Validators\FileBrowser\PathValidator;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @property string action
 * @property string source
 * @property string|null path
 * @property string|null from
 * @property string|null name
 * @property string|null newname
 */
class FileBrowserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'action' => ['required', 'string'],
            'path' => ['nullable', 'string', new PathValidator()],
            'source' => ['required', 'string'],
            'from' => ['sometimes', 'string'],
            'name' => [$this->nameRule(), 'string'],
            'newname' => [$this->newNameRule(), 'string'],
        ];
    }

    private function nameRule(): string
    {
        return 'required_if:action,'
            . implode(
                ',',
                [
                    FolderCreate::ACTION,
                    FolderRemove::ACTION,
                    FolderRemove::ACTION,
                ]
            );
    }

    private function newNameRule(): string
    {
        return 'required_if:action,' . FolderRename::ACTION;
    }

    public function getDto(): FileBrowserDto
    {
        return FileBrowserDto::byRequest($this);
    }
}
