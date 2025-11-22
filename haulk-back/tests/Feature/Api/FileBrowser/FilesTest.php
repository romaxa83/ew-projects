<?php

namespace Tests\Feature\Api\FileBrowser;

use App\Services\FileBrowser\Actions\Files;
use Illuminate\Contracts\Container\BindingResolutionException;

/**
 * @group FileBrowser
 */
class FilesTest extends AbstractFileBrowserTest
{
    public function test_it_browse_files_success(): void
    {
        $superadmin = $this->loginAsCarrierSuperAdmin();
        $prefix = $superadmin->getCompany()->getFileBrowserPrefix();
        $this->fileBrowser->setFileBrowserPrefix($prefix);

        $str = 'file1.txt';
        $this->fileBrowser->put($str, 'some text');

        $this->postJson(
            route('filebrowser.browse'),
            [
                'action' => Files::ACTION,
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
