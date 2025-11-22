<?php

namespace Tests\Feature\Api\FileBrowser;

use App\Services\FileBrowser\Actions\FileRemove;
use Illuminate\Contracts\Container\BindingResolutionException;

/**
 * @group FileBrowser
 */
class FileRemoveTest extends AbstractFileBrowserTest
{
    public function test_it_delete_file_success(): void
    {
        $superadmin = $this->loginAsCarrierSuperAdmin();
        $this->setFileBrowserPrefixForUser($superadmin);

        $file = 'deletingFile.txt';
        $content = 'File content for delete';
        $this->fileBrowser->makeFile($file, $content);

        $this->postJson(
            route('filebrowser.browse'),
            [
                'action' => FileRemove::ACTION,
                'source' => 'default',
                'path' => '',
                'name' => $file
            ]
        )
            ->assertOk()
            ->assertJson(
                [
                    'success' => true,
                    'data' => [
                        'code' => 220,
                    ],
                ]
            );

        $this->assertFalse($this->fileBrowser->exists($file));
    }

    public function test_it_remove_file_from_sub_folder_success(): void
    {
        $superadmin = $this->loginAsCarrierSuperAdmin();
        $this->setFileBrowserPrefixForUser($superadmin);

        $sub = 'sub1';
        $this->fileBrowser->makeDirectory($sub);

        $file = 'file_from_sub_folder.txt';
        $content = 'File content data';
        $removingFile = $sub . DIRECTORY_SEPARATOR . $file;
        $this->fileBrowser->makeFile($removingFile, $content);

        $this->postJson(
            route('filebrowser.browse'),
            [
                'action' => FileRemove::ACTION,
                'source' => 'default',
                'path' => $sub,
                'name' => $file
            ]
        )
            ->assertOk()
            ->assertJson(
                [
                    'success' => true,
                    'data' => [
                        'code' => 220,
                    ],
                ]
            );

        $this->assertFalse($this->fileBrowser->exists($removingFile));
    }
}
