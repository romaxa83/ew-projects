<?php

namespace Tests\Unit\Models\Files;

use App\Models\Users\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\DiskDoesNotExist;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\FileDoesNotExist;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\FileIsTooBig;
use Spatie\MediaLibrary\Models\Media;
use Tests\TestCase;

class UserFilesTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @var string
     */
    protected $content = 'new text content in file.';

    /**
     * @throws DiskDoesNotExist
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    public function test_it_attach_file_to_user_model()
    {
        $mediaTable = config('medialibrary.table_name');

        /** @var User $user */
        $user = User::factory()->create();

        $this->assertDatabaseMissing($mediaTable, ['model_id' => $user->id]);

        $forUpload = UploadedFile::fake()->createWithContent('test.txt', $this->content);
        $user->addMedia($forUpload)
            ->withCustomProperties(['file_name' => $forUpload->name])
            ->toMediaCollection();

        $this->assertDatabaseHas($mediaTable, ['model_id' => $user->id]);

        /** @var Media $remote */
        $remote = $user->getFirstMedia();

        $this->assertNotNull($remote->getFullUrl());

//        $this->assertTrue(Storage::exists($remote->getPath()));
//        $this->assertEquals($this->content, Storage::get($remote->getPath()));
    }

    /**
     * @throws DiskDoesNotExist
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    public function test_file_has_public_access()
    {
        /** @var User $user */
        $user = User::factory()->create();

        $forUpload = UploadedFile::fake()->createWithContent('test.txt', $this->content);
        $user->addMedia($forUpload)
            ->withCustomProperties(['file_name' => $forUpload->name])
            ->toMediaCollection();

        $remote = $user->getFirstMedia();

        $this->assertNotNull($remote);
        $this->assertNotNull($remote->getFullUrl());
    }
}
