<?php


namespace Tests\Feature\Api\Users\Dispatcher;


use App\Models\Users\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Tests\TestCase;

class DispatcherCreateTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public function test_it_create_new_dispatcher_success()
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

        $role = $this->getRoleRepository()->findByName(User::DISPATCHER_ROLE);
        $roleAttributes = [
            'role_id' => $role->id,
        ];

        $this->assertDatabaseMissing(User::TABLE_NAME, $dbAttributes);

        $this->postJson(route('users.store'), $attributes + $roleAttributes)
            ->assertStatus(Response::HTTP_CREATED);

        $this->assertDatabaseHas(User::TABLE_NAME, $dbAttributes);
    }

    public function test_it_not_create_dispatcher_for_not_permitted()
    {
        self::markTestSkipped();
        $this->loginAsCarrierDispatcher();
        $attributes = [
            'full_name' => 'Full Name',
            'phone' => '1-541-754-3010',
            'email' => $this->faker->email,
        ];

        $this->postJson(route('users.store'), $attributes)
            ->assertOk();
    }

    /**
     * @param $attributes
     * @param $expectErrors
     * @dataProvider formSubmitDataProvider
     */
    public function test_it_see_validation_message_on_submit_dispatcher_create($attributes, $dbAttributes, $expectErrors)
    {
        $this->loginAsCarrierSuperAdmin();

        $this->assertDatabaseMissing(User::TABLE_NAME, $dbAttributes);

        $this->postJson(route('users.store'), $attributes)
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
                    'email' => null,
                ],
                [
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'email' => null,
                ],
                [
                    [
                        'source' => ['parameter' => 'role_id'],
                        'title' => 'The Role field is required.',
                        'status' => Response::HTTP_UNPROCESSABLE_ENTITY
                    ],
                ]
            ],
            [
                [
                    'full_name' => $fullName,
                    'email' => null,
                ],
                [
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'email' => null,
                ],
                []
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
                    'email' => $email,
                    'phone' => $phone,
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
