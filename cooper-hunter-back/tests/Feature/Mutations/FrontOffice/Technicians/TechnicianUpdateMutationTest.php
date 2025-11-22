<?php

namespace Tests\Feature\Mutations\FrontOffice\Technicians;

use App\GraphQL\Mutations\FrontOffice\Technicians\TechnicianUpdateMutation;
use App\Models\Auth\MemberPhoneVerification;
use App\Models\Technicians\Technician;
use App\Permissions\Technicians\TechnicianUpdatePermission;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\Permissions\RoleHelperTrait;

class TechnicianUpdateMutationTest extends TestCase
{
    use DatabaseTransactions;
    use RoleHelperTrait;

    public const MUTATION = TechnicianUpdateMutation::NAME;

    public function test_technician_can_update_profile(): void
    {
        $technician = $this->loginAsTechnician()->assignRole(
            $this->generateRole('technician update', [TechnicianUpdatePermission::KEY], Technician::GUARD)
        );

        $code = MemberPhoneVerification::factory()->withAccessToken()->create();

        $query = sprintf(
            'mutation {
                %s (
                    state_id: "%s"
                    country_code: "%s"
                    license: "%s"
                    first_name: "%s"
                    last_name: "%s"
                    email: "%s"
                    phone: "%s"
                    sms_access_token: "%s"
                    credentials: {
                        current_password: "password"
                        password: "%s"
                        password_confirmation: "%s"
                    }
                ) {
                    id
                    epa_license
                    hvac_license
                    first_name
                    last_name
                    email
                    phone
                    email_verified_at
                    lang
                }
            }',
            self::MUTATION,
            $technician->state->id,
            $technician->country->country_code,
            '123123123',
            $newFirstName = 'new name',
            $newLastName = 'new last name',
            $technician->email,
            $technician->phone,
            $code->access_token,
            $newPassword = 'Password123',
            $newPassword,
        );

        $this->postGraphQL(compact('query'))
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        self::MUTATION => [
                            'id' => $technician->id,
                            'first_name' => $newFirstName,
                            'last_name' => $newLastName,
                            'email' => $technician->email,
                            'phone' => $technician->phone,
                            'email_verified_at' => $technician->email_verified_at,
                            'lang' => $technician->lang,
                        ]
                    ]
                ]
            );

        $technician->refresh();

        self::assertNotNull($technician->phone);
        self::assertNotNull($technician->phone_verified_at);
    }
}
