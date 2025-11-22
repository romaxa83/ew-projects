<?php


namespace Tests\Feature\Api\Users\Accountant;


use App\Models\Users\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Tests\TestCase;

class AccountantCreateTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public function test_it_create_new_accountant_success()
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

        $role = $this->getRoleRepository()->findByName(User::ACCOUNTANT_ROLE);

        $this->assertDatabaseMissing(User::TABLE_NAME, $dbAttributes);

        $this->postJson(route('users.store'), $attributes + ['role_id' => $role->id])
            ->assertCreated();

        $this->assertDatabaseHas(User::TABLE_NAME, $dbAttributes);
    }

    /**
     * @param $attributes
     * @param $expectErrors
     * @dataProvider formSubmitDataProvider
     */
    public function test_it_see_validation_message_on_submit_accountant($attributes, $dbAttributes, $expectErrors)
    {
        $this->loginAsCarrierSuperAdmin();

        $role = $this->getRoleRepository()->findByName(User::ACCOUNTANT_ROLE);

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
            [
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
            [
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
            [
                [
                    'full_name' => null,
                    'email' => $email,
                    'phone' => $phone,
                ],
                [
                    'first_name' => null,
                    'last_name' => null,
                    'phone' => $email,
                    'email' => $phone,
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
}
