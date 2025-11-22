<?php

namespace Tests\Unit\Events\Alerts;

use App\Dto\Technicians\TechnicianDto;
use App\Enums\Alerts\AlertModelEnum;
use App\Enums\Alerts\AlertTechnicianEnum;
use App\Models\Admins\Admin;
use App\Models\Alerts\Alert;
use App\Models\Alerts\AlertRecipient;
use App\Models\Locations\Country;
use App\Models\Technicians\Technician;
use App\Services\Technicians\TechnicianService;
use Carbon\Carbon;
use Database\Factories\Locations\CountryFactory;
use Exception;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\Traits\Permissions\AdminManagerHelperTrait;

class TechnicianUpdateEventTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;
    use AdminManagerHelperTrait;

    /**
     * @throws Exception
     */
    public function test_success_moderation(): void
    {
        $technician = Technician::factory()
            ->create();

        $this->assertFalse($technician->is_verified);

        $technician->is_verified = true;
        $technician->save();

        $this->assertDatabaseHas(
            Alert::class,
            [
                'type' => AlertModelEnum::TECHNICIAN . '_' . AlertTechnicianEnum::MODERATION_READY,
                'model_id' => $technician->id,
                'model_type' => $technician::MORPH_NAME
            ]
        );

        $this->assertDatabaseHas(
            AlertRecipient::class,
            [
                'recipient_id' => $technician->id,
                'recipient_type' => $technician::MORPH_NAME
            ]
        );
    }

    /**
     * @throws Exception
     */
    public function test_send_for_re_moderation(): void
    {
        $technician = Technician::factory()
            ->verified()
            ->create();

        $admin = Admin::factory()
            ->create();

        $this->assertTrue($technician->is_verified);

        $technician->hvac_license = $this->faker->lexify;
        $technician->save();

        $this->assertDatabaseHas(
            Alert::class,
            [
                'type' => AlertModelEnum::TECHNICIAN . '_' . AlertTechnicianEnum::RE_MODERATION,
                'model_id' => $technician->id,
                'model_type' => $technician::MORPH_NAME
            ]
        );

        $this->assertDatabaseHas(
            AlertRecipient::class,
            [
                'recipient_id' => $technician->id,
                'recipient_type' => $technician::MORPH_NAME
            ]
        );

        $this->assertDatabaseHas(
            Alert::class,
            [
                'type' => AlertModelEnum::TECHNICIAN . '_' . AlertTechnicianEnum::NEW_RE_MODERATION,
                'model_id' => $technician->id,
                'model_type' => $technician::MORPH_NAME
            ]
        );

        $this->assertDatabaseHas(
            AlertRecipient::class,
            [
                'recipient_id' => $admin->id,
                'recipient_type' => $admin::MORPH_NAME
            ]
        );
    }

    /**
     * @throws Exception
     */
    public function test_email_verification(): void
    {
        $technician = Technician::factory()
            ->certified()
            ->verified()
            ->create();

        $this->assertTrue($technician->isEmailVerified());

        $technician->email = $this->faker->email;
        $technician->save();

        $this->assertDatabaseHas(
            Alert::class,
            [
                'type' => AlertModelEnum::TECHNICIAN . '_' . AlertTechnicianEnum::EMAIL_VERIFICATION_PROCESS,
                'model_id' => $technician->id,
                'model_type' => $technician::MORPH_NAME
            ]
        );

        $technician->email_verified_at = Carbon::now();
        $technician->save();

        $this->assertDatabaseHas(
            Alert::class,
            [
                'type' => AlertModelEnum::TECHNICIAN . '_' . AlertTechnicianEnum::EMAIL_VERIFICATION_READY,
                'model_id' => $technician->id,
                'model_type' => $technician::MORPH_NAME
            ]
        );
    }

    public function test_registration(): void
    {
        $service = resolve(TechnicianService::class);

        $admin = Admin::factory()
            ->create();

        $country = Country::first();
        try {
            $technician = $service->register(
                TechnicianDto::byArgs(
                    [
                        'state_id' => 1,
                        'country_code' => $country->country_code,
                        'license' => $this->faker->lexify,
                        'first_name' => $this->faker->firstName,
                        'last_name' => $this->faker->lastName,
                        'email' => $this->faker->email,
                        'password' => $this->faker->password,
                        'phone' => $this->faker->e164PhoneNumber,
                        'lang' => 'en'
                    ]
                )
            );
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }

        $this->assertDatabaseHas(
            Alert::class,
            [
                'type' => AlertModelEnum::TECHNICIAN . '_' . AlertTechnicianEnum::REGISTRATION,
                'model_id' => $technician->id,
                'model_type' => $technician::MORPH_NAME
            ]
        );

        $this->assertDatabaseHas(
            AlertRecipient::class,
            [
                'recipient_id' => $admin->id,
                'recipient_type' => $admin::MORPH_NAME
            ]
        );
    }
}
