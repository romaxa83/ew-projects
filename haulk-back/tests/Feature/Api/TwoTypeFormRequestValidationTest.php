<?php

namespace Tests\Feature\Api;

use App\Models\Users\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Tests\TestCase;

class TwoTypeFormRequestValidationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public function test_it_has_validation_message_on_submit_with_special_parameter()
    {
        $this->loginAsCarrierSuperAdmin();

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

        $this->assertDatabaseMissing(User::TABLE_NAME, $dbAttributes);

        $this->postJson(route('users.store'), $attributes, ['validate_only' => true])
            ->assertStatus(Response::HTTP_OK);

        $this->assertDatabaseMissing(User::TABLE_NAME, $dbAttributes);
    }

    public function test_it_validate_only_transmitted_fields()
    {
        $this->loginAsCarrierSuperAdmin();

        $attributes = [
            'full_name' => null,
            'phone' => null,
        ];

        $this->postJson(route('users.store'), $attributes, ['validate_only' => true])
            ->assertStatus(Response::HTTP_OK)
            ->assertJson(
                [
                    'data' => [
                        [
                            'source' => ['parameter' => 'full_name'],
                            'title' => 'The Name field is required.',
                            'status' => Response::HTTP_OK
                        ],
                    ]
                ]
            );
    }
}
