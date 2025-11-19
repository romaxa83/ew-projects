<?php

declare(strict_types=1);

namespace Wezom\Admins\Tests\Feature\Mutations\Back;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use JsonException;
use Wezom\Admins\GraphQL\Mutations\Back\BackAdminLogin;
use Wezom\Admins\GraphQL\Mutations\Back\BackAdminRefreshToken;
use Wezom\Admins\Models\Admin;
use Wezom\Admins\Testing\TestCase;
use Wezom\Core\Testing\QueryBuilder\GraphQLQuery;

class AdminRefreshTokenTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = BackAdminRefreshToken::NAME;

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

        $result = $this->postGraphQL(
            GraphQLQuery::mutation(self::MUTATION)
                ->args([
                    'refreshToken' => $data['refreshToken'],
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

        [self::MUTATION => $data] = $result->json('data');

        self::assertArrayHasKey('accessToken', $data);
        self::assertArrayHasKey('refreshToken', $data);
        self::assertArrayHasKey('accessExpiresIn', $data);
        self::assertArrayHasKey('refreshExpiresIn', $data);
        self::assertArrayHasKey('tokenType', $data);
    }
}
