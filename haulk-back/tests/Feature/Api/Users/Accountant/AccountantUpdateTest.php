<?php


namespace Tests\Feature\Api\Users\Accountant;


use App\Models\Users\User;
use Exception;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class AccountantUpdateTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    /**
     * @throws Exception
     */
    public function test_it_not_update_only_validate()
    {
        /** @var User $accountant */
        $attributes = [
            'full_name' => 'Full Name',
            'phone' => '1-541-754-3010',
            'email' => $this->faker->email,
        ];

        $dbAttributes = [
            'first_name' => 'Full',
            'last_name' => 'Name',
            'phone' => '1-541-754-3010',
            'email' => $this->faker->email,
        ];

        $role = $this->getRoleRepository()->findByName(User::ACCOUNTANT_ROLE);

        $accountant = User::factory()->create($dbAttributes);
        $accountant->assignRole(User::ACCOUNTANT_ROLE);

        $this->loginAsCarrierSuperAdmin();

        $newAttributes = $attributes;
        $newAttributes['full_name'] = 'Updated Name';

        $newDbAttributes = $dbAttributes;
        $newDbAttributes['first_name'] = 'Updated';
        $newDbAttributes['last_name'] = 'Name';

        $this->assertDatabaseMissing(User::TABLE_NAME, $newDbAttributes);
        $this->postJson(
            route('users.update', $accountant->id),
            $newAttributes + ['role_id' => $role->id],
            ['validate_only' => true]
        )
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
    public function test_it_update_accountant_success()
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

        /** @var User $accountant */
        $accountant = User::factory()->create($dbAttributes);
        $accountant->assignRole(User::ACCOUNTANT_ROLE);

        $role = $this->getRoleRepository()->findByName(User::ACCOUNTANT_ROLE);

        $this->assertDatabaseHas(User::TABLE_NAME, $dbAttributes);
        $this->assertDatabaseHas(
            config('permission.table_names.model_has_roles'),
            [
                'model_id' => $accountant->id,
                'model_type' => User::class,
            ]
        );

        $this->loginAsCarrierSuperAdmin();

        $newAttributes = $attributes;
        $newAttributes['full_name'] = 'New Full Name';

        $newDbAttributes = $dbAttributes;
        $newDbAttributes['first_name'] = 'New';
        $newDbAttributes['last_name'] = 'Full Name';

        $this->assertDatabaseMissing(User::TABLE_NAME, $newDbAttributes);

        $this->postJson(route('users.update', $accountant->id), $newAttributes + ['role_id' => $role->id])
            ->assertOk();

        $this->assertDatabaseHas(User::TABLE_NAME, $newDbAttributes);
    }

    public function test_it_update_accountant_with_avatar_success()
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

        /** @var User $accountant */
        $accountant = User::factory()->create($dbAttributes);
        $accountant->assignRole(User::ACCOUNTANT_ROLE);

        $role = $this->getRoleRepository()->findByName(User::ACCOUNTANT_ROLE);

        $this->assertDatabaseHas(User::TABLE_NAME, $dbAttributes);
        $this->assertDatabaseHas(
            config('permission.table_names.model_has_roles'),
            [
                'model_id' => $accountant->id,
                'model_type' => User::class,
            ]
        );

        $data = [
            resolve(User::class)->getImageField() => UploadedFile::fake()->image('profile_photo.jpg'),
        ];

        $this->loginAsCarrierAccountant($accountant);
        $this->postJson(route('profile.upload-photo'), $data)
            ->assertOk();

        $this->loginAsCarrierSuperAdmin();

        $newAttributes = $attributes;
        $newAttributes['full_name'] = 'New Full Name';

        $newDbAttributes = $dbAttributes;
        $newDbAttributes['first_name'] = 'New';
        $newDbAttributes['last_name'] = 'Full Name';

        $this->assertDatabaseMissing(User::TABLE_NAME, $newDbAttributes);

        $this->postJson(route('users.update', $accountant->id), $newAttributes + ['role_id' => $role->id])
            ->assertOk();

        $this->assertDatabaseHas(User::TABLE_NAME, $newDbAttributes);
    }
}
