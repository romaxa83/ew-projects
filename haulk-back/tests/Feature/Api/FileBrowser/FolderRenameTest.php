<?php

namespace Tests\Feature\Api\FileBrowser;

use App\Services\FileBrowser\Actions\FolderRename;

/**
 * @group FileBrowser
 */
class FolderRenameTest extends AbstractFileBrowserTest
{
    public function test_it_rename_folder_success(): void
    {
        $superadmin = $this->loginAsCarrierSuperAdmin();
        $prefix = $superadmin->getCompany()->getFileBrowserPrefix();
        $this->fileBrowser->setFileBrowserPrefix($prefix);

        $from = 'deletingDir1';
        $to = 'deletingDir2';

        $this->fileBrowser->makeDirectory($from);

        $this->assertFalse($this->fileBrowser->exists($to));

        $this->postJson(
            route('filebrowser.browse'),
            [
                'action' => FolderRename::ACTION,
                'source' => 'default',
                'path' => '',
                'name' => $from,
                'newname' => $to,
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

        $this->assertFalse($this->fileBrowser->exists($from));
        $this->assertTrue($this->fileBrowser->exists($to));
    }

    public function test_it_rename_sub_directory_success(): void
    {
        $superadmin = $this->loginAsCarrierSuperAdmin();
        $prefix = $superadmin->getCompany()->getFileBrowserPrefix();
        $this->fileBrowser->setFileBrowserPrefix($prefix);

        $sub = 'sub1';

        $from = 'deletingDir1';
        $to = 'deletingDir2';

        $this->fileBrowser->makeDirectory($sub);
        $fromSub = $sub . DIRECTORY_SEPARATOR . $from;

        $toSub = $sub . DIRECTORY_SEPARATOR . $to;
        $this->fileBrowser->makeDirectory($fromSub);

        $this->assertFalse($this->fileBrowser->exists($toSub));

        $this->postJson(
            route('filebrowser.browse'),
            [
                'action' => FolderRename::ACTION,
                'source' => 'default',
                'path' => $sub,
                'name' => $from,
                'newname' => $to,
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

        $this->assertFalse($this->fileBrowser->exists($fromSub));
        $this->assertTrue($this->fileBrowser->exists($toSub));
    }

    public function test_it_rename_file_to_exist_name(): void
    {
        $superadmin = $this->loginAsCarrierSuperAdmin();
        $prefix = $superadmin->getCompany()->getFileBrowserPrefix();
        $this->fileBrowser->setFileBrowserPrefix($prefix);

        $dir = 'dir';
        $this->fileBrowser->makeDirectory($dir);

        $existDir = 'exist_dir';
        $this->fileBrowser->makeDirectory($existDir);

        $this->postJson(
            route('filebrowser.browse'),
            [
                'action' => FolderRename::ACTION,
                'source' => 'default',
                'newname' => $existDir,
                'name' => $dir,
            ]
        )
            ->assertOk()
            ->assertJson(
                [
                    'success' => false,
                    'data' => [
                        'code' => 422,
                        'messages' => [
                            '/' . $existDir . ' - is already exists!',
                        ],
                    ],
                ]
            );
    }
}
