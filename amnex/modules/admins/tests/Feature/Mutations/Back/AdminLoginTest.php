<?php

declare(strict_types=1);

namespace Wezom\Admins\Tests\Feature\Mutations\Back;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use JsonException;
use Wezom\Admins\GraphQL\Mutations\Back\BackAdminLogin;
use Wezom\Admins\Models\Admin;
use Wezom\Admins\Testing\TestCase;
use Wezom\Core\Testing\QueryBuilder\GraphQLQuery;

class AdminLoginTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = BackAdminLogin::NAME;

    /**
     * @throws JsonException
     */
    public function testItLoginSuccess(): void
    {
        $email = 'admin@example.com';
        $password = 'password';

        Admin::factory()->new(['email' => $email])->create();

        $result = $this->postGraphQL(
            GraphQLQuery::mutation(self::MUTATION)
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

        [self::MUTATION => $data] = $result->json('data');

        self::assertArrayHasKey('accessToken', $data);
        self::assertArrayHasKey('refreshToken', $data);
        self::assertArrayHasKey('accessExpiresIn', $data);
        self::assertArrayHasKey('refreshExpiresIn', $data);
        self::assertArrayHasKey('tokenType', $data);
    }

    /**
     * @throws JsonException
     */
    public function testItTryToLoginWithNonExistsCredentials(): void
    {
        $this->postGraphQL(
            GraphQLQuery::mutation(self::MUTATION)
                ->args([
                    'email' => 'notexists_email@example.com',
                    'password' => 'not_exists_password',
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
            ->assertOk()
            ->assertHasValidationMessage('password', __('admins::auth.admin.failed'));
    }
}
