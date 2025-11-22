<?php

namespace Tests\Feature\Mutations\FrontOffice\Dealers;

use App\GraphQL\Mutations\FrontOffice\Members\MemberLoginMutation;
use App\ValueObjects\Email;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Builders\Dealers\DealerBuilder;
use Tests\TestCase;

class DealerLoginMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public const MUTATION = MemberLoginMutation::NAME;

    protected DealerBuilder $dealerBuilder;

    protected function setUp(): void
    {
        parent::setUp();
        $this->dealerBuilder = resolve(DealerBuilder::class);
        $this->passportInit();
    }

    /** @test */
    public function login_dealer(): void
    {
        $email = new Email('dealer@example.com');
        $password = 'password';

        $this->dealerBuilder->setData([
            'email' => $email,
        ])->setPassword($password)->create();

        $data = [
            'username' => $email,
            'password' => $password,
        ];

        $this->postGraphQL([
            'query' => $this->getQuery($data)
        ])
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        self::MUTATION => [
                            'access_token',
                            'refresh_token',
                            'access_expires_in',
                            'refresh_expires_in',
                            'token_type',
                        ],
                    ],
                ]
            );
    }

    /** @test */
    public function fail_wrong_email(): void
    {
        $email = new Email('dealer@example.com');
        $password = 'password';

        $this->dealerBuilder->setData([
            'email' => $email,
        ])->setPassword($password)->create();

        $data = [
            'username' => 'test@gmail.com',
            'password' => $password,
        ];

        $this->postGraphQL([
            'query' => $this->getQuery($data)
        ])
            ->assertJson([
                'errors' => [
                    ["message" => "validation"]
                ],
            ]);
    }

    /** @test */
    public function fail_wrong_password(): void
    {
        $email = new Email('dealer@example.com');
        $password = 'password';

        $this->dealerBuilder->setData([
            'email' => $email,
        ])->setPassword($password)->create();

        $data = [
            'username' => $email,
            'password' => $password . '89',
        ];

        $this->postGraphQL([
            'query' => $this->getQuery($data)
        ])
            ->assertJson([
                'errors' => [
                    ["message" => "validation"]
                ],
            ]);
    }

    protected function getQuery(array $data): string
    {
        return sprintf(
            '
            mutation {
                %s (
                    username: "%s"
                    password: "%s"
                ) {
                    access_token
                    refresh_token
                    access_expires_in
                    refresh_expires_in
                    token_type
                }
            }',
            self::MUTATION,
            data_get($data, 'username'),
            data_get($data, 'password'),
        );
    }
}
