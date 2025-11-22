<?php

namespace Tests\Unit\Events\Alerts;

use App\Dto\Warranty\WarrantyRegistrationDto;
use App\Enums\Alerts\AlertModelEnum;
use App\Enums\Alerts\AlertSystemEnum;
use App\Enums\Projects\Systems\WarrantyStatus;
use App\Models\Alerts\Alert;
use App\Models\Alerts\AlertRecipient;
use App\Models\Locations\Country;
use App\Models\Locations\State;
use App\Models\Technicians\Technician;
use App\Services\Warranty\WarrantyService;
use Exception;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\Traits\Models\ProjectCreateTrait;

class SystemUpdateEventTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;
    use ProjectCreateTrait;

    /**
     * @throws Exception
     */
    public function test_change_warranty_status(): void
    {
        $member = Technician::factory()
            ->certified()
            ->verified()
            ->create();

        $this->loginAsTechnicianWithRole($member);

        $system = $this->createProjectForMember($member)
            ->systems()
            ->first();

        $this->assertEquals(WarrantyStatus::WARRANTY_NOT_REGISTERED, $system->warranty_status);

        $service = resolve(WarrantyService::class);

        $state = State::first();
        $country = Country::first();

        $service->register(
            $member,
            $system,
            WarrantyRegistrationDto::byArgs(
                [
                    'user' => [
                        'first_name' => $this->faker->firstName,
                        'last_name' => $this->faker->lastName,
                        'email' => $this->faker->email
                    ],
                    'address' => [
                        'street' => $this->faker->streetName,
                        'country_code' => $country->country_code,
                        'state_id' => $state->id,
                        'city' => $this->faker->city,
                        'zip' => $this->faker->lexify
                    ],
                    'product' => [
                        'purchase_date' => $this->faker->date,
                        'installation_date' => $this->faker->date,
                        'installer_license_number' => $this->faker->numerify,
                        'purchase_place' => $this->faker->lexify
                    ]
                ]
            )
        );

        $this->assertDatabaseHas(
            Alert::class,
            [
                'type' => AlertModelEnum::SYSTEM . '_' . AlertSystemEnum::WARRANTY_STATUS,
                'model_id' => $system->id,
                'model_type' => $system::MORPH_NAME
            ]
        );

        $this->assertDatabaseHas(
            AlertRecipient::class,
            [
                'recipient_id' => $member->id,
                'recipient_type' => Technician::MORPH_NAME
            ]
        );
    }
}
