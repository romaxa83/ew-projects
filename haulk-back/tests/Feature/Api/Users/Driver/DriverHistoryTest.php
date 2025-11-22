<?php

namespace Api\Users\Driver;

use App\Models\Files\File;
use App\Models\Locations\State;
use App\Models\Users\DriverInfo;
use App\Models\Users\DriverLicense;
use App\Models\Users\User;
use App\Models\Users\UserComment;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Tests\Helpers\Traits\UserFactoryHelper;
use Tests\TestCase;

class DriverHistoryTest extends TestCase
{
    use DatabaseTransactions;
    use UserFactoryHelper;

    public function test_history(): void
    {
        $this->loginAsCarrierSuperAdmin();

        $userId = $this->create();

        $this->getJson(route('users.history', $userId))
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $this->getJson(route('users.history-detailed', $userId))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonCount(34, 'data.0.histories');

        $this->update($userId);

        $this->getJson(route('users.history', $userId))
            ->assertOk()
            ->assertJsonCount(2, 'data');

        $this->getJson(route('users.history-detailed', $userId))
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonCount(7, 'data.0.histories');

        $this->addComment($userId);

        $this->getJson(route('users.history', $userId))
            ->assertOk()
            ->assertJsonCount(3, 'data');

        $this->getJson(route('users.history-detailed', $userId))
            ->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonCount(1, 'data.0.histories');

        $comment = UserComment::query()->where('user_id', $userId)->first();
        $this->deleteComment($userId, $comment->id);

        $this->getJson(route('users.history', $userId))
            ->assertOk()
            ->assertJsonCount(4, 'data');

        $this->getJson(route('users.history-detailed', $userId))
            ->assertOk()
            ->assertJsonCount(4, 'data')
            ->assertJsonCount(1, 'data.0.histories');

        $this->addAttachment($userId);

        $this->getJson(route('users.history', $userId))
            ->assertOk()
            ->assertJsonCount(5, 'data');

        $this->getJson(route('users.history-detailed', $userId))
            ->assertOk()
            ->assertJsonCount(5, 'data')
            ->assertJsonCount(1, 'data.0.histories');

        $attachment = File::query()->where('model_id', $userId)->first();
        $this->deleteAttachment($userId, $attachment->id);

        $this->getJson(route('users.history', $userId))
            ->assertOk()
            ->assertJsonCount(6, 'data');

        $this->getJson(route('users.history-detailed', $userId))
            ->assertOk()
            ->assertJsonCount(6, 'data')
            ->assertJsonCount(1, 'data.0.histories');

        $this->deleteMvrDocument($userId);

        $this->getJson(route('users.history', $userId))
            ->assertOk()
            ->assertJsonCount(7, 'data');

        $this->getJson(route('users.history-detailed', $userId))
            ->assertOk()
            ->assertJsonCount(7, 'data')
            ->assertJsonCount(1, 'data.0.histories');

        $this->deletePreviousDriverLicense($userId);

        $this->getJson(route('users.history', $userId))
            ->assertOk()
            ->assertJsonCount(8, 'data');

        $this->getJson(route('users.history-detailed', $userId))
            ->assertOk()
            ->assertJsonCount(8, 'data')
            ->assertJsonCount(1, 'data.0.histories');
    }

    private function create(): int
    {
        $email = $this->faker->email;
        $attributes = [
            'first_name' => 'First',
            'last_name' => 'Last',
            'phone' => '1-541-754-3010',
            'phone_extension' => '123',
            'email' => $email,
        ];

        $driversRole = $this->getRoleRepository()->findByName(User::DRIVER_ROLE);

        $roles = [
            'role_id' => $driversRole->id,
            'owner_id' => $this->authenticatedUser->id,
        ];

        $state = factory(State::class)->create();

        $driverInfoAttributes = [
            'notes' => 'test notes',
            'driver_license' => [
                'license_number' => '3434-dfdf',
                'issuing_state_id' => $state->id,
                'issuing_date' => now()->format('m/d/Y'),
                'expiration_date' => now()->addDays(2)->format('m/d/Y'),
                'category' => DriverLicense::CATEGORY_C,
                'category_name' => null,
                DriverLicense::ATTACHED_DOCUMENT_FILED_NAME => UploadedFile::fake()->image('image1.jpg'),
            ],
            'previous_driver_license' => [
                'license_number' => 'sdfsdf343-w',
                'is_usa' => false,
                'issuing_country' => 'Canada',
                'issuing_state_id' => null,
                'issuing_date' => now()->format('m/d/Y'),
                'expiration_date' => now()->addDay()->format('m/d/Y'),
                'category' => DriverLicense::CATEGORY_OTHER,
                'category_name' => 'ABC',
                DriverLicense::ATTACHED_DOCUMENT_FILED_NAME => UploadedFile::fake()->create('file.pdf'),
            ],
            'medical_card' => [
                'card_number' => '23435dfg',
                'issuing_date' => now()->format('m/d/Y'),
                'expiration_date' => now()->addDays(5)->format('m/d/Y'),
                DriverInfo::ATTACHED_MEDICAL_CARD_FILED_NAME => UploadedFile::fake()->create('file.pdf'),
            ],
            'mvr' => [
                'reported_date' => now()->format('m/d/Y'),
                DriverInfo::ATTACHED_MVR_FILED_NAME => UploadedFile::fake()->image('file.png'),
            ],
            'has_company' => true,
            'company_info' =>  [
                'name' => 'test company',
                'ein' => 'dfsdf654',
                'address' => 'address test',
                'city' => 'cityname',
                'zip' => '23545',
            ],
        ];

        $response = $this->postJson(route('v2.carrier.users.store'), $attributes + $driverInfoAttributes + $roles)
            ->assertCreated();

        return $response['data']['id'];
    }

    private function update(int $userId): void
    {
        $email = $this->faker->email;
        $attributes = [
            'first_name' => 'First',
            'last_name' => 'Last',
            'phone' => '1-541-754-3010',
            'phone_extension' => '123',
            'email' => $email,
        ];

        $driversRole = $this->getRoleRepository()->findByName(User::OWNER_DRIVER_ROLE);

        $roles = [
            'role_id' => $driversRole->id,
            'owner_id' => $this->authenticatedUser->id,
        ];

        $state = factory(State::class)->create();

        $driverInfoAttributes = [
            'notes' => 'test notes',
            'driver_license' => [
                'license_number' => '3434-dfdf',
                'issuing_state_id' => $state->id,
                'issuing_date' => now()->format('m/d/Y'),
                'expiration_date' => now()->addDays(2)->format('m/d/Y'),
                'category' => DriverLicense::CATEGORY_D,
                'category_name' => null,
                DriverLicense::ATTACHED_DOCUMENT_FILED_NAME => UploadedFile::fake()->image('image4.jpg'),
            ],
            'previous_driver_license' => [
                'license_number' => 'sdfsdf343-w',
                'is_usa' => false,
                'issuing_country' => 'Canada',
                'issuing_state_id' => null,
                'issuing_date' => now()->format('m/d/Y'),
                'expiration_date' => now()->addDay()->format('m/d/Y'),
                'category' => DriverLicense::CATEGORY_OTHER,
                'category_name' => 'ABC',
            ],
            'medical_card' => [
                'card_number' => '23435dfg',
                'issuing_date' => now()->format('m/d/Y'),
                'expiration_date' => now()->addDays(5)->format('m/d/Y'),
            ],
            'mvr' => [
                'reported_date' => now()->format('m/d/Y'),
            ],
            'has_company' => true,
            'company_info' =>  [
                'name' => 'test company',
                'ein' => 'dfsdf654',
                'address' => 'address test',
                'city' => 'cityname2',
                'zip' => '23545',
            ],
        ];

        $this->postJson(route('v2.carrier.users.update', $userId), $attributes + $driverInfoAttributes + $roles)
            ->assertOk();
    }

    private function addComment(int $userId): void
    {
        $this->postJson(
            route('users.comments.store', $userId),
            [
                'comment' => 'comment text',
            ]
        )->assertCreated();
    }

    private function deleteComment(int $userId, int $commentId): void
    {
        $this->deleteJson(route('users.comments.destroy', [$userId, $commentId]))
            ->assertStatus(Response::HTTP_NO_CONTENT);
    }

    private function addAttachment(int $userId): void
    {
        $attributes = [
            'attachment' => UploadedFile::fake()->createWithContent('info.txt', 'Some text for user file'),
        ];
        $this->postJson(route('users.attachments', $userId), $attributes)
            ->assertOk();
    }

    private function deleteAttachment(int $userId, int $attachmentId): void
    {
        $this->deleteJson(route('users.delete-attachments', [$userId, $attachmentId]))
            ->assertNoContent();
    }

    private function deleteMvrDocument(int $userId): void
    {
        $this->deleteJson(route('users.delete-mvr-document', $userId))
            ->assertStatus(Response::HTTP_NO_CONTENT);
    }

    private function deletePreviousDriverLicense(int $userId): void
    {
        $this->deleteJson(route('users.delete-previous-driver-license-document', $userId))
            ->assertStatus(Response::HTTP_NO_CONTENT);
    }

    public function test_profile_updating(): void
    {
        $user = $this->loginAsCarrierOwner();

        $attributes = [
            'first_name' => 'FullOther',
            'last_name' => 'NameOther',
            'phone' => $user->phone,
            'email' => $user->email,
        ];

        $this->putJson(route('v2.carrier.profile.update'), $attributes)
            ->assertOk();

        $this->loginAsCarrierSuperAdmin();

        $this->getJson(route('users.history', $user))
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $this->getJson(route('users.history-detailed', $user))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonCount(2, 'data.0.histories');
    }

    public function test_users_list(): void
    {
        $this->loginAsCarrierSuperAdmin();
        $userId = $this->create();

        $this->loginAsCarrierAdmin();
        $this->update($userId);

        $this->loginAsCarrierDispatcher();
        $this->addComment($userId);

        $this->loginAsCarrierSuperAdmin();
        $this->getJson(route('users.history-users-list', $userId))
            ->assertOk()
            ->assertJsonCount(3, 'data');
    }
}
