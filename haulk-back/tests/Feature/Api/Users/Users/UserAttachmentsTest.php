<?php

namespace Tests\Feature\Api\Users\Users;

use App\Models\Files\File;
use App\Models\Users\User;
use App\Services\Users\UserService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\DiskDoesNotExist;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\FileDoesNotExist;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\FileIsTooBig;
use Tests\Helpers\Traits\UserFactoryHelper;
use Tests\TestCase;

class UserAttachmentsTest extends TestCase
{
    use DatabaseTransactions;
    use UserFactoryHelper;

    private UserService $service;

    public function test_it_attach_to_user_new_file()
    {
        $this->loginAsCarrierSuperAdmin();

        $user = $this->dispatcherFactory();

        $attributes = [
            'attachment' => UploadedFile::fake()->createWithContent('info.txt', 'Some text for user file'),
        ];
        $response = $this->postJson(route('users.attachments', $user), $attributes)
            ->assertOk();

        $userResource = json_to_array($response->content())['data'];

        $this->assertCount(1, $userResource[User::ATTACHMENT_COLLECTION_NAME]);
    }

    public function test_it_has_error_for_unauthorized_attach_user()
    {
        $user = $this->dispatcherFactory();

        $attributes = [
            'attachment' => UploadedFile::fake()->createWithContent('info.txt', 'Some text for user file'),
        ];
        $this->postJson(route('users.attachments', $user), $attributes)
            ->assertUnauthorized();
    }

    public function test_it_has_forbidden_for_not_permitted_user()
    {
        $this->loginAsCarrierDispatcher();

        $user = $this->dispatcherFactory();

        $attributes = [
            'attachment' => UploadedFile::fake()->createWithContent('info.txt', 'Some text for user file'),
        ];

        $this->postJson(route('users.attachments', $user), $attributes)
            ->assertForbidden();
    }

    /**
     * @throws DiskDoesNotExist
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    public function test_it_delete_attachment_file_success()
    {
        $user = $this->dispatcherFactory();

        $attachment = UploadedFile::fake()->createWithContent('info.txt', 'Some text for user file');

        $this->service->setLoggedUser($user)->addAttachment($user, $attachment);

        $attachments = $user->getAttachments();
        $attachment = array_shift($attachments);

        $this->assertDatabaseHas(
            File::TABLE_NAME,
            [
                'id' => $attachment->id
            ]
        );

        $this->loginAsCarrierSuperAdmin();

        $this->deleteJson(route('users.delete-attachments', ['user' => $user->id, 'id' => $attachment->id]))
            ->assertNoContent();

        $this->assertDatabaseMissing(
            File::TABLE_NAME,
            [
                'id' => $attachment->id
            ]
        );
    }

    /**
     * @throws DiskDoesNotExist
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    public function test_it_has_unauthorized_error_for_not_logged_deleter()
    {
        $user = $this->dispatcherFactory();

        $attachment = UploadedFile::fake()->createWithContent('info.txt', 'Some text for user file');

        $this->service->setLoggedUser($user)->addAttachment($user, $attachment);

        $attachments = $user->getAttachments();
        $attachment = array_shift($attachments);
        $this->deleteJson(route('users.delete-attachments', ['user' => $user->id, 'id' => $attachment->id]))
            ->assertUnauthorized();
    }

    /**
     * @throws DiskDoesNotExist
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    public function test_it_has_forbidden_error_for_not_permitted_deleter()
    {
        $this->loginAsCarrierDispatcher();

        $user = $this->dispatcherFactory();

        $attachment = UploadedFile::fake()->createWithContent('info.txt', 'Some text for user file');

        $this->service->setLoggedUser($user)->addAttachment($user, $attachment);

        $attachments = $user->getAttachments();
        $attachment = array_shift($attachments);
        $this->deleteJson(route('users.delete-attachments', ['user' => $user->id, 'id' => $attachment->id]))
            ->assertForbidden();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = resolve(UserService::class);
    }
}
