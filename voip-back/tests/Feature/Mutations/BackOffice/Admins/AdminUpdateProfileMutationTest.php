<?php

namespace Tests\Feature\Mutations\BackOffice\Admins;

use App\GraphQL\Mutations\BackOffice\Admins\AdminUpdateProfileMutation;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AdminUpdateProfileMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public const MUTATION = AdminUpdateProfileMutation::NAME;

    protected array $data = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->data = [
            'name' => $this->faker->name,
            'email' => $this->faker->safeEmail,
            'password' => 'Password123',
        ];
    }

    /** @test*/
    public function success_update_all_fields(): void
    {
        $admin = $this->loginAsSuperAdmin();

        $data = $this->data;
        $data['notify'] = 'false';

        $this->assertNotEquals($admin->name, data_get($data, 'name'));
        $this->assertNotEquals($admin->email, data_get($data, 'email'));
        $this->assertFalse(password_verify(data_get($data, 'Password1234'), $admin->password));

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($data)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'id' => $admin->id,
                        'name' => data_get($data, 'name'),
                        'email' => data_get($data, 'email'),
                    ],
                ]
            ])
        ;

        $admin->refresh();

        $this->assertTrue(password_verify(data_get($data, 'password'), $admin->password));
    }

    public function getQueryStr(array $data): string
    {
        return sprintf(
            '
            mutation {
                %s (
                    name: "%s"
                    email: "%s"
                    password: "%s"
                ) {
                    id
                    name
                    email
                }
            }',
            self::MUTATION,
            data_get($data, 'name'),
            data_get($data, 'email'),
            data_get($data, 'password'),
        );
    }

    /** @test*/
    public function success_update_only_name(): void
    {
        $admin = $this->loginAsSuperAdmin();

        $data = $this->data;
        $data['notify'] = 'true';

        $this->assertNotEquals($admin->name, data_get($data, 'name'));
        $this->assertNotEquals($admin->email, data_get($data, 'email'));
        $this->assertFalse(password_verify(data_get($data, 'Password1234'), $admin->password));

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStrName($data)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'id' => $admin->id,
                        'name' => data_get($data, 'name'),
                        'email' => $admin->email->getValue(),
                    ],
                ]
            ])
        ;

        $admin->refresh();

        $this->assertFalse(password_verify(data_get($data, 'Password1234'), $admin->password));
    }

    public function getQueryStrName(array $data): string
    {
        return sprintf(
            '
            mutation {
                %s (
                    name: "%s"
                ) {
                    id
                    name
                    email
                }
            }',
            self::MUTATION,
            data_get($data, 'name'),
        );
    }

    /** @test*/
    public function success_update_only_email(): void
    {
        $admin = $this->loginAsSuperAdmin();

        $data = $this->data;

        $this->assertNotEquals($admin->name, data_get($data, 'name'));
        $this->assertNotEquals($admin->email, data_get($data, 'email'));
        $this->assertFalse(password_verify(data_get($data, 'Password1234'), $admin->password));

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStrEmail($data)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'id' => $admin->id,
                        'name' => $admin->name,
                        'email' => data_get($data, 'email'),
                    ],
                ]
            ])
        ;

        $admin->refresh();

        $this->assertFalse(password_verify(data_get($data, 'Password1234'), $admin->password));
    }

    public function getQueryStrEmail(array $data): string
    {
        return sprintf(
            '
            mutation {
                %s (
                    email: "%s"
                ) {
                    id
                    name
                    email
                }
            }',
            self::MUTATION,
            data_get($data, 'email'),
        );
    }

    /** @test*/
    public function success_update_only_password(): void
    {
        $admin = $this->loginAsSuperAdmin();

        $data = $this->data;
        $data['password'] = 'Password1234';

        $this->assertNotEquals($admin->name, data_get($data, 'name'));
        $this->assertNotEquals($admin->email, data_get($data, 'email'));
        $this->assertFalse(password_verify(data_get($data, 'password'), $admin->password));

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStrPassword($data)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'id' => $admin->id,
                        'name' => $admin->name,
                        'email' => $admin->email->getValue(),
                    ],
                ]
            ])
        ;

        $admin->refresh();

        $this->assertTrue(password_verify(data_get($data, 'password'), $admin->password));
    }

    public function getQueryStrPassword(array $data): string
    {
        return sprintf(
            '
            mutation {
                %s (
                    password: "%s"
                ) {
                    id
                    name
                    email
                }
            }',
            self::MUTATION,
            data_get($data, 'password'),
        );
    }
}
