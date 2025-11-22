<?php

namespace Tests\Feature\Http\Api\V1\Common\FileBrowser;

use App\Services\FileBrowser\Actions\Files;
use Illuminate\Contracts\Container\BindingResolutionException;

/**
 * @group FileBrowser
 */
class FilesTest extends AbstractFileBrowserTest
{
    public function test_it_browse_files_success(): void
    {
        $superadmin = $this->loginUserAsSuperAdmin();
        $prefix = $superadmin->getFileBrowserPrefix();
        $this->fileBrowser->setFileBrowserPrefix($prefix);

        $str = 'file1.txt';
        $this->fileBrowser->put($str, 'some text');

        $this->postJson(route('api.v1.filebrowser.browse'), [
            'action' => Files::ACTION,
            'source' => 'default',
        ])
            ->assertOk()
            ->assertJson(
                [
                    'success' => true,
                    'code' => 220,
                    'data' => [
                        'sources' => [
                            'default' => [
                                'baseurl' => config('filesystems.disks.local.url').'/filebrowser/' . $prefix . '/',
                                'path' => '',
                                'files' => [
                                    [
                                        'file' => 'file1.txt',
                                        'size' => '0.009 kb',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ]
            );
    }

}
