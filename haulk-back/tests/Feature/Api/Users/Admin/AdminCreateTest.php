<?php

namespace Tests\Feature\Api\Users\Admin;

use App\Models\Forms\Draft;
use App\Models\Users\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Tests\TestCase;

class AdminCreateTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public function test_it_create_new_admin_success()
    {
        $this->loginAsCarrierSuperAdmin();

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

        $role = $this->getRoleRepository()->findByName(User::ADMIN_ROLE);

        $roleAttributes = [
            'role_id' => $role->id,
        ];

        $this->assertDatabaseMissing(User::TABLE_NAME, $dbAttributes);

        $this->postJson(route('users.store'), $attributes + $roleAttributes)
            ->assertStatus(Response::HTTP_CREATED);

        $this->assertDatabaseHas(User::TABLE_NAME, $dbAttributes);
    }

    /**
     * @param $attributes
     * @param $expectErrors
     * @dataProvider formSubmitDataProvider
     */
    public function test_it_see_validation_message_on_submit($attributes, $dbAttributes, $expectErrors)
    {
        $this->loginAsCarrierSuperAdmin();

        $role = $this->getRoleRepository()->findByName(User::ADMIN_ROLE);

        $this->assertDatabaseMissing(User::TABLE_NAME, $dbAttributes);
        $this->postJson(route('users.store'), $attributes + ['role_id' => $role->id])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJson(
                [
                    'errors' => $expectErrors,
                ]
            );

        $this->assertDatabaseMissing(User::TABLE_NAME, $dbAttributes);
    }

    public function formSubmitDataProvider(): array
    {
        $fullName = 'Name';
        $firstName = 'Name';
        $lastName = '';
        $phone = '1-541-754-3010';
        $email = 'chernenko.v@wezom.com.ua';

        return [
            'create admin form data 1' => [
                [
                    'full_name' => $fullName,
                    'phone' => null,
                    'email' => null,
                ],
                [
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'phone' => null,
                    'email' => null,
                ],
                [
                    [
                        'source' => ['parameter' => 'email'],
                        'title' => 'The Email field is required.',
                        'status' => Response::HTTP_UNPROCESSABLE_ENTITY
                    ],
                ]
            ],
            'create admin form data 2' => [
                [
                    'full_name' => $fullName,
                    'phone' => $phone,
                    'email' => null,
                ],
                [
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'phone' => $phone,
                    'email' => null,
                ],
                [
                    [
                        'source' => ['parameter' => 'email'],
                        'title' => 'The Email field is required.',
                        'status' => Response::HTTP_UNPROCESSABLE_ENTITY
                    ],
                ]
            ],
            'create admin form data 3' => [
                [
                    'full_name' => null,
                    'email' => $email,
                    'phone' => $phone,
                ],
                [
                    'first_name' => null,
                    'last_name' => null,
                    'phone' => $phone,
                    'email' => $email,
                ],
                [
                    [
                        'source' => ['parameter' => 'full_name'],
                        'title' => 'The Name field is required.',
                        'status' => Response::HTTP_UNPROCESSABLE_ENTITY
                    ],
                ]
            ],
        ];
    }

    public function test_it_create_draft_when_validate_form()
    {
        $this->loginAsCarrierSuperAdmin();

        $attributes = [
            'full_name' => 'Full Name',
            'phone' => '1-541-754-3010',
            $email = $this->faker->email,
        ];

        $path = route('users.store');

        $headers = [
            config('requestvalidationonly.header_key') => true,
            config('draft.header_key') => $path,
        ];


        $draftAttributes = [
            'user_id' => $this->authenticatedUser->id,
            'path' => $path,
        ];

        $this->assertDatabaseMissing(Draft::TABLE_NAME, $draftAttributes);

        $this->postJson(route('users.store'), $attributes, $headers)
            ->assertOk();

        $this->assertDatabaseHas(Draft::TABLE_NAME, $draftAttributes);

    }
}
