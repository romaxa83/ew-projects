<?php

namespace Tests\Feature\Api\BodyShop\Profile;

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
        $attributes = [
            'first_name' => 'Full',
            'last_name' => 'Name',
            'phone' => '1-541-754-3010',
            'email' => $this->faker->email,
            'carrier_id' => null,
        ];
        $user = User::factory()->create($attributes);
        $user->assignRole(User::BSSUPERADMIN_ROLE);

        $this->loginAsBodyShopSuperAdmin($user);

        $newAttributes = $attributes;
        $newAttributes['first_name'] = 'New';
        $newAttributes['last_name'] = 'Full Name';

        $this->assertDatabaseMissing(User::TABLE_NAME, $newAttributes);
        $this->putJson(route('body-shop.profile.update'), $newAttributes, ['validate_only' => true])
            ->assertOk()
            ->assertJson(
                [
                    'data' => [],
                ]
            );

        $this->assertDatabaseMissing(User::TABLE_NAME, $newAttributes);
    }

    /**
     * @throws Exception
     */
    public function test_it_update_profile_success()
    {
        $attributes = [
            'first_name' => 'Full',
            'last_name' => 'Name',
            'phone' => '1-541-754-3010',
            'email' => $this->faker->unique()->email,
            'carrier_id' => null,
        ];

        /** @var User $user */
        $user = User::factory()->create($attributes);
        $user->assignRole(User::BSSUPERADMIN_ROLE);

        $this->loginAsCarrierSuperAdmin($user);

        $this->assertDatabaseHas(User::TABLE_NAME, $attributes);
        $this->assertDatabaseHas(
            config('permission.table_names.model_has_roles'),
            [
                'model_id' => $user->id,
                'model_type' => User::class,
            ]
        );

        $newAttributes = $attributes;
        $newAttributes['first_name'] = 'New';
        $newAttributes['last_name'] = 'Full Name';
        $this->assertDatabaseMissing(User::TABLE_NAME, $newAttributes);
        $this->putJson(route('body-shop.profile.update'), $newAttributes)
            ->assertOk();
        $this->assertDatabaseHas(User::TABLE_NAME, $newAttributes);
    }

    public function test_it_delete_profile_photo_success()
    {
        $this->test_it_upload_profile_photo_success();

        $this->deleteJson(route('body-shop.profile.delete-photo'))
            ->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertNull($this->authenticatedUser->getFirstImage());
    }

    public function test_it_upload_profile_photo_success()
    {
        $this->loginAsBodyShopAdmin();

        $data = [
            resolve(User::class)->getImageField() => UploadedFile::fake()->image('profile_photo.jpg'),
        ];

        $this->assertNull($this->authenticatedUser->getFirstImage());

        $this->postJson(route('body-shop.profile.upload-photo'), $data)
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => ['photo'],
                ]
            );

        $this->assertNotNull($this->authenticatedUser->getFirstImage());
    }

    public function test_auth_admin_can_change_password(): void
    {
        $user = User::factory()->create(['password' => 'password', 'carrier_id' => null]);
        $user->assignRole(User::BSSUPERADMIN_ROLE);

        $this->loginAsBodyShopSuperAdmin($user);

        $newPassword = 'password123';

        $attrs = [
            'current_password' => 'password',
            'password' => $newPassword,
            'password_confirmation' => $newPassword,
        ];

        $this->putJson(route('body-shop.profile.change-password'), $attrs)
            ->assertOk();

        $user->fresh();

        self::assertTrue($user->passwordCompare($newPassword));
    }
}
