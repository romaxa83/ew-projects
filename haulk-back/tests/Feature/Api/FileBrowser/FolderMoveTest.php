<?php

namespace Tests\Feature\Api\FileBrowser;

use App\Services\FileBrowser\Actions\FolderMove;
use Illuminate\Contracts\Container\BindingResolutionException;

class FolderMoveTest extends AbstractFileBrowserTest
{
    public function test_it_move_folder_to_other_folder_success(): void
    {
        $superadmin = $this->loginAsCarrierSuperAdmin();
        $this->setFileBrowserPrefixForUser($superadmin);

        $dir1 = 'dir1';
        $dir2 = 'dir2';

        $this->fileBrowser->makeDirectory($dir1);
        $this->fileBrowser->makeDirectory($dir2);

        $this->postJson(
            route('filebrowser.browse'),
            [
                'action' => FolderMove::ACTION,
                'source' => 'default',
                'path' => $dir2,
                'from' => $dir1,
            ]
        )
            ->assertOk()
            ->assertJson(
                [
                    'success' => false,
                    'data' => [
                        'code' => 422,
                        'messages' => [
                            'Moving directories is not possible!',
                        ]
                    ],
                ]
            );
    }
}
