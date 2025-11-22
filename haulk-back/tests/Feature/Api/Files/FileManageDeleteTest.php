<?php

namespace Tests\Feature\Api\Files;

use App\Models\Files\File;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\DiskDoesNotExist;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\FileDoesNotExist;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\FileIsTooBig;
use Storage;
use Tests\TestCase;

class FileManageDeleteTest extends TestCase
{
    use DatabaseTransactions;

    public function test_it_delete_only_unauthorized_users()
    {
        $media = File::factory()->create();

        $this->deleteJson(route('files.delete', $media))
            ->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function test_it_delete_only_permitted_users()
    {
        $media = File::factory()->create();

        $this->loginAsCarrierDispatcher();

        $this->deleteJson(route('files.delete', $media))
            ->assertForbidden();
    }

    /**
     *
     * @throws DiskDoesNotExist
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    public function test_it_deleted_by_super_admin()
    {
        $this->loginAsCarrierSuperAdmin();

        $content = 'Some content.';
        $uploaded = UploadedFile::fake()->createWithContent('file_for_user.txt', $content);
        $this->authenticatedUser->addMedia($uploaded)->toMediaCollection();

        $media = $this->authenticatedUser->getFirstMedia();
        if (config('filesystems.default') === 's3') {
            $this->assertTrue(Storage::exists($media->getPath()));
        }

        $this->deleteJson(route('files.delete', $media))
            ->assertNoContent();

        $this->authenticatedUser->unsetRelation('media');

        $this->assertNull($this->authenticatedUser->getFirstMedia());
        $this->assertFalse(Storage::exists($media->getPath()));
    }
}
