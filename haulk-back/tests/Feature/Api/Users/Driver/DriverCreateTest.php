<?php


namespace Tests\Feature\Api\Users\Driver;


use App\Models\Users\DriverInfo;
use App\Models\Users\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Tests\TestCase;

class DriverCreateTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public function test_it_create_new_driver_success()
    {
        $this->withoutExceptionHandling();
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

        $driverInfoAttributes = [
            'notes' => $this->faker->randomLetter,
        ];

        $driversRole = $this->getRoleRepository()->findByName(User::DRIVER_ROLE);

        $roles = [
            'role_id' => $driversRole->id,
            'owner_id' => $this->authenticatedUser->id,
        ];


        $this->assertDatabaseMissing(User::TABLE_NAME, $dbAttributes + ['owner_id' => $this->authenticatedUser->id]);
        $this->assertDatabaseMissing(DriverInfo::TABLE_NAME, $driverInfoAttributes);

        $this->postJson(route('users.store'), $attributes + $driverInfoAttributes + $roles)
            ->assertCreated();

        $this->assertDatabaseHas(User::TABLE_NAME, $dbAttributes + ['owner_id' => $this->authenticatedUser->id]);
        $this->assertDatabaseHas(DriverInfo::TABLE_NAME, $driverInfoAttributes);
    }

    /**
     * @param $attributes
     * @param $expectErrors
     * @dataProvider formSubmitDataProvider
     */
    public function test_it_see_validation_message_on_submit_driver_create($attributes, $expectErrors)
    {
        $this->loginAsCarrierSuperAdmin();

        $role = $this->getRoleRepository()->findByName(User::DRIVER_ROLE);

        $attributes = array_merge(
            $attributes,
            [
                'role_id' => $role->id
            ]
        );

        $this->postJson(route('users.store'), $attributes)
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJson(
                [
                    'errors' => $expectErrors,
                ]
            );
    }

    public function test_it_not_create_driver_for_not_permitted()
    {
        $this->loginAsCarrierDispatcher();
        $attributes = [
            'full_name' => 'Full Name',
            'phone' => '1-541-754-3010',
            'email' => $this->faker->email,
        ];
        $driverInfoAttributes = [
            'driver_license_number' => $this->faker->randomLetter,
        ];

        $this->postJson(route('users.store'), $attributes + $driverInfoAttributes)
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function formSubmitDataProvider(): array
    {
        $fullName = 'Name';
        $phone = '1-541-754-3010';
        $email = 'chernenko.v@wezom.com.ua';
        $trailerCapacity = 5;

        return [
            'driver create form data 1' => [
                [
                    'full_name' => $fullName,
                    'phone' => null,
                    'email' => null,
                ],
                [
                    [
                        'source' => ['parameter' => 'email'],
                        'title' => 'The Email field is required.',
                        'status' => Response::HTTP_UNPROCESSABLE_ENTITY
                    ],
                    [
                        'source' => ['parameter' => 'owner_id'],
                        'title' => 'The Assign to dispatcher field is required.',
                        'status' => Response::HTTP_UNPROCESSABLE_ENTITY
                    ],
                ]
            ],
            'driver create form data 2' => [
                [
                    'full_name' => $fullName,
                    'phone' => $phone,
                    'email' => null,
                ],
                [
                    [
                        'source' => ['parameter' => 'email'],
                        'title' => 'The Email field is required.',
                        'status' => Response::HTTP_UNPROCESSABLE_ENTITY
                    ],
                    [
                        'source' => ['parameter' => 'owner_id'],
                        'title' => 'The Assign to dispatcher field is required.',
                        'status' => Response::HTTP_UNPROCESSABLE_ENTITY
                    ],

                ]
            ],
            'driver create form data 3' => [
                [
                    'full_name' => null,
                    'email' => $email,
                    'phone' => $phone,
                ],
                [
                    [
                        'source' => ['parameter' => 'full_name'],
                        'title' => 'The Name field is required.',
                        'status' => Response::HTTP_UNPROCESSABLE_ENTITY
                    ],
                    [
                        'source' => ['parameter' => 'owner_id'],
                        'title' => 'The Assign to dispatcher field is required.',
                        'status' => Response::HTTP_UNPROCESSABLE_ENTITY
                    ],
                ]
            ],
        ];
    }
}
