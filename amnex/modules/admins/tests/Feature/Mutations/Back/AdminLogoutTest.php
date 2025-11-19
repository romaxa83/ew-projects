<?php

declare(strict_types=1);

namespace Wezom\Admins\Tests\Feature\Mutations\Back;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use JsonException;
use Wezom\Admins\GraphQL\Mutations\Back\BackAdminLogin;
use Wezom\Admins\GraphQL\Mutations\Back\BackAdminLogout;
use Wezom\Admins\Models\Admin;
use Wezom\Admins\Testing\TestCase;
use Wezom\Core\Testing\QueryBuilder\GraphQLQuery;

class AdminLogoutTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = BackAdminLogout::NAME;

    /**
     * @throws JsonException
     */
    public function testItLogoutSuccess(): void
    {
        $email = 'admin@example.com';
        $password = 'password';

        Admin::factory()->new(['email' => $email])->create();
        $result = $this->postGraphQL(
            GraphQLQuery::mutation(BackAdminLogin::NAME)
                ->args([
                    'email' => $email,
                    'password' => $password,
                    'remember' => true,
                ])
                ->select([
                    'refreshToken',
                    'accessExpiresIn',
                    'refreshExpiresIn',
                    'tokenType',
                    'accessToken',
                ])
                ->make()
        )
            ->assertOk();

        [BackAdminLogin::NAME => $data] = $result->json('data');

        $query = GraphQLQuery::mutation(self::MUTATION)->make();

        $this->postGraphQL($query, ['Authorization' => 'Bearer ' . $data['accessToken']])
            ->assertOk()
            ->assertJson(['data' => [self::MUTATION => true]]);

        $this->postGraphQL($query, ['Authorization' => 'Bearer ' . $data['accessToken']])
            ->assertOk()
            ->assertJson(['data' => [self::MUTATION => false]]);
    }
}
