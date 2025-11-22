<?php

namespace Tests\Feature\Api\FileBrowser;

use App\Services\FileBrowser\Actions\FileMove;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\Response;
use Storage;

/**
 * @group FileBrowser
 */
class FileMoveTest extends AbstractFileBrowserTest
{
    public function test_it_rename_folder_success(): void
    {
        $superadmin = $this->loginAsCarrierSuperAdmin();
        $this->setFileBrowserPrefixForUser($superadmin);

        $file = 'newfile1';
        $content = 'New file content';

        $this->fileBrowser->makeFile($file, $content);

        $newDir = 'dir1';

        $newFilePath = $newDir . DIRECTORY_SEPARATOR . $file;

        $this->fileBrowser->makeDirectory($newDir);

        $this->assertFalse($this->fileBrowser->exists($newFilePath));

        $this->postJson(
            route('filebrowser.browse'),
            [
                'action' => FileMove::ACTION,
                'source' => 'default',
                'path' => $newDir,
                'from' => $file,
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
        $this->assertTrue($this->fileBrowser->exists($newFilePath));
    }

    public function test_it_has_move_error_for_exists_file(): void
    {
        $superadmin = $this->loginAsCarrierSuperAdmin();
        $this->setFileBrowserPrefixForUser($superadmin);

        $file = 'file.txt';
        $content = 'New file content';
        $this->fileBrowser->makeFile($file, $content);

        $newDir = 'dir1';
        $this->fileBrowser->makeDirectory($newDir);

        $existFile = $newDir . DIRECTORY_SEPARATOR . $file;
        $this->fileBrowser->makeFile($existFile, $content);

        $this->postJson(
            route('filebrowser.browse'),
            [
                'action' => FileMove::ACTION,
                'source' => 'default',
                'path' => $newDir,
                'from' => $file,
            ]
        )
            ->assertOk()
            ->assertJson(
                [
                    'success' => false,
                    'data' => [
                        'code' => 422,
                        'messages' => [
                            'dir1/file.txt - is already exists!'
                        ]
                    ],
                ]
            );
    }

    public function test_it_has_error_if_path_for_move_is_under_root(): void
    {
        $superadmin = $this->loginAsCarrierSuperAdmin();
        $this->setFileBrowserPrefixForUser($superadmin);

        $file = 'file.txt';
        $this->fileBrowser->makeFile($file, 'some file content text');

        $this->postJson(
            route('filebrowser.browse'),
            [
                'action' => FileMove::ACTION,
                'source' => 'default',
                'path' => '/../',
                'from' => $file,
            ]
        )
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJson(
                [
                    'errors' => [
                        [
                            'source' => [
                                'parameter' => 'path',
                            ],
                            'title' => 'path - error! Can\'t write file below root directory',
                            'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                        ]
                    ]
                ]
            );

        $this->assertFalse(Storage::exists($file));
    }

    public function test_it_get_unauthorized_error(): void
    {
        $this->postJson(
            route('filebrowser.browse'),
            [
                'action' => FileMove::ACTION,
                'source' => 'default',
                'path' => '/../',
                'from' => 'some_path',
            ])
            ->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

}
