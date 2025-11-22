<?php

namespace Tests\Feature\Api\Users\Profile;

use App\Models\Users\User;
use Exception;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ProfileUpdateTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    /**
     * @throws Exception
     */
    public function test_it_not_update_profile_only_validate()
    {
        /** @var User $user */
        $email = $this->faker->email;
        $attributes = [
            'full_name' => 'Full Name',
            'phone' => '1-541-754-3010',
            'email' => $email,
        ];

        $dbAttributes = [
            'first_name' => 'Full',
            'last_name' => 'Name',
            'phone' => '1-541-754-3010',
            'email' => $email,
        ];

        $user = User::factory()->create($dbAttributes);
        $user->assignRole(User::SUPERADMIN_ROLE);

        $this->loginAsCarrierSuperAdmin($user);

        $newAttributes = $attributes;
        $newAttributes['first_name'] = 'Full Name';

        $newDbAttributes = $dbAttributes;
        $newDbAttributes['first_name'] = 'New';
        $newDbAttributes['last_name'] = 'Full Name';

        $this->assertDatabaseMissing(User::TABLE_NAME, $newDbAttributes);
        $this->putJson(route('profile.update'), $newAttributes, ['validate_only' => true])
            ->assertOk()
            ->assertJson(
                [
                    'data' => [],
                ]
            );

        $this->assertDatabaseMissing(User::TABLE_NAME, $newDbAttributes);
    }

    /**
     * @throws Exception
     */
    public function test_it_update_profile_success()
    {
        $email = $this->faker->email;
        $attributes = [
            'full_name' => 'Full Name',
            'phone' => '1-541-754-3010',
            'email' => $email,
        ];

        $dbAttributes = [
            'first_name' => 'Full',
            'last_name' => 'Name',
            'phone' => '1-541-754-3010',
            'email' => $email,
        ];

        /** @var User $user */
        $user = User::factory()->create($dbAttributes);
        $user->assignRole(User::SUPERADMIN_ROLE);

        $this->loginAsCarrierSuperAdmin($user);

        $this->assertDatabaseHas(User::TABLE_NAME, $dbAttributes);
        $this->assertDatabaseHas(
            config('permission.table_names.model_has_roles'),
            [
                'model_id' => $user->id,
                'model_type' => User::class,
            ]
        );

        $newAttributes = $attributes;
        $newAttributes['full_name'] = 'New Full Name';

        $newDbAttributes = $dbAttributes;
        $newDbAttributes['first_name'] = 'New';
        $newDbAttributes['last_name'] = 'Full Name';

//        $newAttributes['email'] = $this->faker->unique()->email;
        $this->assertDatabaseMissing(User::TABLE_NAME, $newDbAttributes);
        $this->putJson(route('profile.update'), $newAttributes)
            ->assertOk();
        $this->assertDatabaseHas(User::TABLE_NAME, $newDbAttributes);
    }

    public function test_it_validate_phones()
    {
        $this->loginAsCarrierSuperAdmin();

        $this->putJson(
            route('profile.update'),
            [
                'phones' => [
                    [
                        'number' => '123',
                        'extension' => '123'
                    ]
                ],
            ],
            [
                config('requestvalidationonly.header_key') => 1,
            ]
        )->assertOk()
            ->assertJson(
                [
                    'data' => [
                        [
                            'source' => ['parameter' => 'phones.0.number'],
                            'title' => 'Phone must be correct usa number.',
                            'status' => 200,
                        ]
                    ]
                ]
            );
    }

    public function test_extension_required_phones_in_array()
    {
        $this->loginAsCarrierSuperAdmin();

        $this->putJson(
            route('profile.update'),
            [
                'phones' => [
                    [
                        'extension' => '123'
                    ]
                ],
            ],
            [
                config('requestvalidationonly.header_key') => 1,
            ]
        )->assertOk()
            ->assertJson(
                [
//                    'data' => [
//                        [
//                            'source' => ['parameter' => 'phones.0.number'],
//                            'title' => 'The phones.0.number field is required when phones.0.extension is present.',
//                        ]
//                    ]
                ]
            );
    }

    public function test_it_delete_profile_photo_success()
    {
        $this->test_it_upload_profile_photo_success();

        $this->deleteJson(route('profile.delete-photo'))
            ->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertNull($this->authenticatedUser->getFirstImage());
    }

    public function test_it_upload_profile_photo_success()
    {
        $this->loginAsCarrierDispatcher();

        $data = [
            resolve(User::class)->getImageField() => UploadedFile::fake()->image('profile_photo.jpg'),
        ];

        $this->assertNull($this->authenticatedUser->getFirstImage());

        $this->postJson(route('profile.upload-photo'), $data)
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => ['photo'],
                ]
            );

        $this->assertNotNull($this->authenticatedUser->getFirstImage());
    }

    public function test_it_not_delete_photo_if_another_user_delete_self_photo()
    {
        $this->test_it_upload_profile_photo_success();
        $anotherUser = $this->authenticatedUser;

        $this->loginAsCarrierSuperAdmin();

        $data = [
            resolve(User::class)->getImageField() => UploadedFile::fake()->image('profile_photo.jpg'),
        ];

        $this->assertNull($this->authenticatedUser->getFirstImage());
        $this->postJson(route('profile.upload-photo'), $data)->assertOk();
        $this->assertNotNull($this->authenticatedUser->getFirstImage());

        $this->deleteJson(route('profile.delete-photo'))->assertStatus(Response::HTTP_NO_CONTENT);
        $this->assertNull($this->authenticatedUser->getFirstImage());
        $this->assertNotNull($image = $anotherUser->getFirstImage());

        if ($this->isStorageS3()) {
            $this->assertNotNull(file_get_contents($image->getFullUrl()));
        }
    }
}
