<?php

namespace Tests\Feature\Api\FileBrowser;

use App\Services\FileBrowser\Actions\Folders;
use Illuminate\Contracts\Container\BindingResolutionException;
use Throwable;

/**
 * @group FileBrowser
 */
class DirectoriesTest extends AbstractFileBrowserTest
{
    /**
     * @throws Throwable
     */
    public function test_it_browse_directories_success(): void
    {
        $superadmin = $this->loginAsCarrierSuperAdmin();
        $prefix = $superadmin->getCompany()->getFileBrowserPrefix();
        $this->fileBrowser->setFileBrowserPrefix($prefix);

        $dir1 = 'dir1';
        $dir2 = 'dir2';

        $this->fileBrowser->makeDirectory($dir1);
        $this->fileBrowser->makeDirectory($dir2);

        $this->postJson(
            route('filebrowser.browse'),
            [
                'action' => Folders::ACTION,
                'source' => 'default',
            ]

        )
            ->assertOk()
            ->assertJson(
                [
                    'success' => true,
                    'code' => 220,
                    'data' => [
                        'sources' => [
                            'default' => [
                                'baseurl' => 'http://localhost/storage/filebrowser/' . $prefix . '/',
                                'path' => '',
                                'folders' => [
                                    '.',
                                    $dir1,
                                    $dir2,
                                ]
                            ]
                        ],
                    ],
                ]
            );
    }

    /**
     * @throws Throwable
     */
    public function test_it_browse_sub_directories_success(): void
    {
        $superadmin = $this->loginAsCarrierSuperAdmin();
        $prefix = $superadmin->getCompany()->getFileBrowserPrefix();
        $this->fileBrowser->setFileBrowserPrefix($prefix);

        $dir1 = 'dir1';
        $dir2 = 'dir1/dir2';

        $this->fileBrowser->makeDirectory($dir1);
        $this->fileBrowser->makeDirectory($dir2);

        $this->postJson(
            route('filebrowser.browse'),
            [
                'action' => Folders::ACTION,
                'source' => 'default',
                'path' => 'dir1'
            ]
        )
            ->assertOk()
            ->assertJson(
                [
                    'success' => true,
                    'code' => 220,
                    'data' => [
                        'sources' => [
                            'dir1' => [
                                'baseurl' => 'http://localhost/storage/filebrowser/' . $prefix . '/dir1',
                                'path' => 'dir1',
                                'folders' => [
                                    '..',
                                    'dir2',
                                ]
                            ]
                        ],
                    ],
                ]
            );
    }
}
