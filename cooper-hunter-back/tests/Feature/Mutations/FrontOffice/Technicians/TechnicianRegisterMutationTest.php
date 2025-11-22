<?php

namespace Tests\Feature\Mutations\FrontOffice\Technicians;

use App\Events\Technicians\TechnicianRegisteredEvent;
use App\GraphQL\Mutations\FrontOffice\Technicians\TechnicianRegisterMutation;
use App\Models\Auth\MemberPhoneVerification;
use App\Models\Locations\Country;
use App\Models\Locations\State;
use App\Models\Technicians\Technician;
use App\Models\Users\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class TechnicianRegisterMutationTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = TechnicianRegisterMutation::NAME;

    public function test_hvac_technician_register_success(): void
    {
        Event::fake();

        $state = State::factory()->hvac()->create();
        $country = Country::first();

        $this->query($state, $country)
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        self::MUTATION => [
                            'refresh_token',
                            'access_expires_in',
                            'refresh_expires_in',
                            'token_type',
                            'access_token',
                        ],
                    ],
                ]
            );

        $this->assertDatabaseCount(Technician::TABLE, 1);
        $this->assertDatabaseHas(
            Technician::TABLE,
            [
                'hvac_license' => '12345',
                'epa_license' => null,
            ]
        );

        Event::assertDispatched(TechnicianRegisteredEvent::class);

        $model = Technician::query()->first();

        $this->assertEquals($model->country->id, $country->id);
    }

    protected function query(State $state, Country $country, string $args = ''): TestResponse
    {
        return $this->postGraphQL(
            [
                'query' => sprintf(
                    'mutation { %s
                        (
                            state_id: "%s",
                            country_code: "%s",
                            license: "12345",
                            first_name: "name",
                            last_name: "surname",
                            email: "example@email.com",
                            password: "Aa12345678",
                            password_confirmation: "Aa12345678",
                            %s
                       )
                       {
                           refresh_token
                           access_expires_in
                           refresh_expires_in
                           token_type
                           access_token
                       }
                    }',
                    self::MUTATION,
                    $state->id,
                    $country->country_code,
                    $args
                )
            ]
        );
    }

    public function test_epa_technician_register_success(): void
    {
        Event::fake();

        $state = State::factory()->epa()->create();
        $country = Country::factory()->create();

        $this->assertDatabaseCount(Technician::TABLE, 0);

        $this->query($state, $country)->assertOk();

        $this->assertDatabaseCount(Technician::TABLE, 1);
        $this->assertDatabaseHas(
            Technician::TABLE,
            [
                'hvac_license' => null,
                'epa_license' => '12345',
            ]
        );

        Event::assertDispatched(TechnicianRegisteredEvent::class);
    }

    public function test_validate_only_registration(): void
    {
        Event::fake();

//        $state = State::factory()->epa()->create();
//        $country = Country::factory()->create();

        $state = State::query()->where([
            'hvac_license' => false,
            'epa_license' => true,
        ])->first();
        $country = Country::first();

        $this->query($state, $country,'validate_only: true')
            ->assertJsonStructure(
                [
                    'errors' => [
                        [
                            'message',
                            'extensions' => []
                        ],
                    ],
                ]
            );

        $this->assertDatabaseMissing(
            User::TABLE,
            [
                'email' => 'example@email.com',
            ]
        );
    }

    public function test_confirm_phone(): void
    {
        Event::fake();

        $code = MemberPhoneVerification::factory()->withAccessToken()->create();
        $state = State::factory()->epa()->create();
        $country = Country::factory()->create();

        $this->query($state, $country, sprintf('sms_access_token: "%s"', $code->access_token))->assertOk();

        $this->assertDatabaseCount(Technician::TABLE, 1);
        $this->assertDatabaseHas(
            Technician::TABLE,
            [
                'hvac_license' => null,
                'epa_license' => '12345',
            ]
        );

        $technician = Technician::query()->where('epa_license', '12345')->first();

        self::assertNotNull($technician->phone_verified_at);

        $this->assertDatabaseMissing(
            MemberPhoneVerification::TABLE,
            [
                'id' => $code->id
            ]
        );
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->passportInit();
    }
}
