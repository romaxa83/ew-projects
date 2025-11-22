<?php

namespace Tests\Feature\V2\Users\Driver;

use App\Models\Files\File;
use App\Models\Locations\State;
use App\Models\Users\DriverInfo;
use App\Models\Users\DriverLicense;
use App\Models\Users\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class UserCreateTest extends TestCase
{
    use DatabaseTransactions;

    public function test_with_full_driver_data(): void
    {
        $this->loginAsCarrierSuperAdmin();

        $email = $this->faker->email;
        $attributes = [
            'first_name' => 'First',
            'last_name' => 'Last',
            'phone' => '1-541-754-3010',
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

        $this->postJson(route('v2.carrier.users.store'), $attributes + $driverInfoAttributes + $roles)
            ->assertCreated();

        $this->assertDatabaseHas(User::TABLE_NAME, $attributes + ['owner_id' => $this->authenticatedUser->id]);
        $this->assertDatabaseHas(
            DriverInfo::TABLE_NAME,
            [
                'notes' => 'test notes',
                'medical_card_number' => '23435dfg',
                'medical_card_issuing_date' => now()->format('Y-m-d'),
                'medical_card_expiration_date' => now()->addDays(5)->format('Y-m-d'),
                'mvr_reported_date' => now()->format('Y-m-d'),
                'has_company' => true,
                'company_name' => 'test company',
                'company_ein' => 'dfsdf654',
                'company_address' => 'address test',
                'company_city' => 'cityname',
                'company_zip' => '23545',
            ]
        );

        $this->assertDatabaseHas(
            DriverLicense::TABLE_NAME,
            [
                'license_number' => '3434-dfdf',
                'issuing_state_id' => $state->id,
                'issuing_date' => now()->format('Y-m-d'),
                'expiration_date' => now()->addDays(2)->format('Y-m-d'),
                'category' => DriverLicense::CATEGORY_C,
                'category_name' => null,
                'type' => DriverLicense::TYPE_CURRENT,
            ]
        );

        $this->assertDatabaseHas(
            DriverLicense::TABLE_NAME,
            [
                'license_number' => 'sdfsdf343-w',
                'issuing_country' => 'Canada',
                'issuing_state_id' => null,
                'issuing_date' => now()->format('Y-m-d'),
                'expiration_date' => now()->addDay()->format('Y-m-d'),
                'category' => DriverLicense::CATEGORY_OTHER,
                'category_name' => 'ABC',
                'type' => DriverLicense::TYPE_PREVIOUS,
            ]
        );

        $this->assertDatabaseHas(
            File::TABLE_NAME,
            [
                'model_type' => DriverInfo::class,
                'collection_name' => DriverInfo::ATTACHED_MVR_FILED_NAME,
            ]
        );

        $this->assertDatabaseHas(
            File::TABLE_NAME,
            [
                'model_type' => DriverInfo::class,
                'collection_name' => DriverInfo::ATTACHED_MEDICAL_CARD_FILED_NAME,
            ]
        );

        $this->assertDatabaseHas(
            File::TABLE_NAME,
            [
                'model_type' => DriverLicense::class,
                'collection_name' => DriverLicense::ATTACHED_DOCUMENT_FILED_NAME,
            ]
        );
    }
}
