<?php

namespace Tests\Feature\Http\Api\V1\Common\FileBrowser;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;

/**
 * @group FileBrowser
 */
class FileUploadTest extends AbstractFileBrowserTest
{
    public function test_it_try_to_upload_image_and_has_unauthorized_error(): void
    {
        $fileName = 'image_name.jpg';
        $file = UploadedFile::fake()->image($fileName);

        $this->postJson(
            route('api.v1.filebrowser.upload'),
            [
                'source' => 'default',
                'files' => [
                    $file,
                ],
            ]
        )
            ->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function test_it_upload_image_success(): void
    {
        $superadmin = $this->loginUserAsSuperAdmin();
        $this->setFileBrowserPrefixForUser($superadmin);

        $fileName = 'image-name.jpg';
        $file = UploadedFile::fake()->image($fileName);

        $this->assertFalse($this->fileBrowser->exists($fileName));

        $this->postJson(route('api.v1.filebrowser.upload'), [
            'source' => 'default',
            'files' => [
                $file,
            ],
        ])
            ->assertOk();

        $this->assertTrue($this->fileBrowser->exists($fileName));
    }

    /**
     * @param $fileName
     * @param $expected
     * @dataProvider uploadDifferentFormatsDataProvider
     */
    public function test_it_upload_different_file_success($fileName, $expected): void
    {
        $superadmin = $this->loginUserAsSuperAdmin();
        $this->setFileBrowserPrefixForUser($superadmin);

        $file = UploadedFile::fake()->create($fileName);

        $this->assertFalse($this->fileBrowser->exists($expected));

        $this->postJson(
            route('api.v1.filebrowser.upload'),
            [
                'source' => 'default',
                'files' => [
                    $file,
                ],
            ]
        )
            ->assertOk();

        $this->assertTrue($this->fileBrowser->exists($expected));
    }

    public static function uploadDifferentFormatsDataProvider(): array
    {
        return [
            ['file-name.txt', 'file-name.txt'],
            ['some-microsoft-xls-file.xls', 'some-microsoft-xls-file.xls'],
            ['some-microsoft-docx-file.docx', 'some-microsoft-docx-file.docx'],
            ['some-microsoft-docx-file.docx.vnd', 'some-microsoft-docx-file.docx'],
        ];
    }
}
