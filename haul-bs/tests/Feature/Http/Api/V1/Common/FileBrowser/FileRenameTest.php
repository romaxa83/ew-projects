<?php

namespace Tests\Feature\Http\Api\V1\Common\FileBrowser;

use App\Services\FileBrowser\Actions\FileRename;
use Illuminate\Contracts\Container\BindingResolutionException;

/**
 * @group FileBrowser
 */
class FileRenameTest extends AbstractFileBrowserTest
{
    public function test_it_has_rename_error_for_exists_file(): void
    {
        $superadmin = $this->loginUserAsSuperAdmin();
        $this->setFileBrowserPrefixForUser($superadmin);

        $file = 'file.txt';

        $this->fileBrowser->makeFile($file, 'some content');

        $existFile = 'exist_file.txt';
        $this->fileBrowser->makeFile($existFile, 'some content');

        $this->postJson(
            route('api.v1.filebrowser.browse'),
            [
                'action' => FileRename::ACTION,
                'source' => 'default',
                'path' => '/',
                'name' => $file,
                'newname' => $existFile,
            ]
        )
            ->assertOk()
            ->assertJson(
                [
                    'success' => false,
                    'data' => [
                        'code' => 422,
                        'messages' => [
                           '/' . $existFile . ' - is already exists!'
                        ]
                    ],
                ]
            );
    }
}
