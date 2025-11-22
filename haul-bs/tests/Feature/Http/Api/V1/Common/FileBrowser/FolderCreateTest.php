<?php

namespace Tests\Feature\Http\Api\V1\Common\FileBrowser;

use App\Services\FileBrowser\Actions\FolderCreate;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Config;

/**
 * @group FileBrowser
 */
class FolderCreateTest extends AbstractFileBrowserTest
{
    public function test_it_create_directory_success(): void
    {
        $superadmin = $this->loginUserAsSuperAdmin();
        $this->setFileBrowserPrefixForUser($superadmin);

        $newDirectory = 'newDirectory';

        $this->assertFalse($this->fileBrowser->exists($newDirectory));

        $this->postJson(
            route('api.v1.filebrowser.browse'),
            [
                'action' => FolderCreate::ACTION,
                'source' => 'default',
                'path' => '',
                'name' => $newDirectory
            ]
        )
            ->assertOk()
            ->assertJson(
                [
                    'success' => true,
                    'code' => 220,
                    'data' => [
                        'messages' => [
                            'Directory created successfully',
                        ],
                    ],
                ]
            );

        $this->assertTrue($this->fileBrowser->exists($newDirectory));
    }

    public function test_it_create_sub_directory_success(): void
    {
        $superadmin = $this->loginUserAsSuperAdmin();
        $this->setFileBrowserPrefixForUser($superadmin);

        $path = 'dir1';
        $this->fileBrowser->makeDirectory($path);

        $newSubDirectory = 'newSubDirectory';
        $fullDirPath = $path . DIRECTORY_SEPARATOR . $newSubDirectory;

        $this->assertFalse($this->fileBrowser->exists($fullDirPath));

        $this->postJson(
            route('api.v1.filebrowser.browse'),
            [
                'action' => FolderCreate::ACTION,
                'source' => 'default',
                'path' => $path,
                'name' => $newSubDirectory
            ]
        )
            ->assertOk()
            ->assertJson(
                [
                    'success' => true,
                    'code' => 220,
                    'data' => [
                        'messages' => [
                            'Directory created successfully',
                        ],
                    ],
                ]
            );

        $this->assertTrue($this->fileBrowser->exists($fullDirPath));
    }

    public function test_it_has_some_max_nesting_level()
    {
        $superadmin = $this->loginUserAsSuperAdmin();
        $this->setFileBrowserPrefixForUser($superadmin);

        Config::set('filebrowser.nesting_limit', 1);

        $path = 'dir1';
        $this->fileBrowser->makeDirectory($path);

        $newSubDirectory = 'newSubDirectory';
        $fullDirPath = $path . DIRECTORY_SEPARATOR . $newSubDirectory;

        $this->assertFalse($this->fileBrowser->exists($fullDirPath));

        $this->postJson(
            route('api.v1.filebrowser.browse'),
            [
                'action' => FolderCreate::ACTION,
                'source' => 'default',
                'path' => $path,
                'name' => $newSubDirectory
            ]
        )
            ->assertOk()
            ->assertJson(
                [
                    'success' => false,
                    'data' => [
                        'code' => 422,
                        'messages' => [
                            'Maximum directory nesting is 1',
                        ],
                    ],
                ]
            );

        $this->assertFalse($this->fileBrowser->exists($fullDirPath));
    }
}
