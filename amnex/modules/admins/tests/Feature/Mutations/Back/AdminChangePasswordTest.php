<?php

declare(strict_types=1);

namespace Wezom\Admins\Tests\Feature\Mutations\Back;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use JsonException;
use Wezom\Admins\GraphQL\Mutations\Back\BackAdminChangePassword;
use Wezom\Admins\Testing\TestCase;
use Wezom\Core\Testing\QueryBuilder\GraphQLQuery;

class AdminChangePasswordTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = BackAdminChangePassword::NAME;

    /**
     * @throws JsonException
     */
    public function testItChangePasswordSuccess(): void
    {
        $admin = $this->loginAsAdmin();

        self::assertTrue(Hash::check('password', $admin->password));

        $result = $this->postGraphQL(
            GraphQLQuery::mutation(self::MUTATION)
                ->args([
                    'current' => 'password',
                    'password' => 'new1password@',
                    'passwordConfirmation' => 'new1password@',
                ])
                ->make()
        )
            ->assertOk();

        [self::MUTATION => $data] = $result->json('data');

        self::assertTrue($data);

        $admin->refresh();

        self::assertTrue(Hash::check('new1password@', $admin->password));
    }

    /**
     * @throws JsonException
     */
    public function testItHasValidationErrorWhenOldPasswordIsNotCorrect(): void
    {
        $admin = $this->loginAsAdmin();

        self::assertTrue(Hash::check('password', $admin->password));

        $this->postGraphQL(
            GraphQLQuery::mutation(self::MUTATION)
                ->args([
                    'current' => 'password1',
                    'password' => 'new1password',
                    'passwordConfirmation' => 'new1password',
                ])
                ->make()
        )
            ->assertOk()
            ->assertHasValidationMessage('current', __('admins::auth.admin.failed'));
    }

    /**
     * @throws JsonException
     */
    public function testItHasErrorForNotAuthUser(): void
    {
        $result = $this->postGraphQL(
            GraphQLQuery::mutation(self::MUTATION)
                ->args([
                    'current' => 'password1',
                    'password' => 'new1password',
                    'passwordConfirmation' => 'new1password',
                ])
                ->make()
        )
            ->assertOk();

        $errors = $result->json('errors');

        self::assertEquals('Unauthenticated.', array_shift($errors)['message']);
    }

    /**
     * @throws JsonException
     */
    public function testItHasValidationErrorWhenBadPasswordConfirmation(): void
    {
        $admin = $this->loginAsAdmin();

        self::assertTrue(Hash::check('password', $admin->password));

        $this->postGraphQL(
            GraphQLQuery::mutation(self::MUTATION)
                ->args([
                    'current' => 'password',
                    'password' => 'new1password@',
                    'passwordConfirmation' => 'new1password@1',
                ])
                ->make()
        )
            ->assertOk()

            ->assertHasValidationMessage(
                'passwordConfirmation',
                __('The password confirmation field must match password.')
            );
    }

    /**
     * @throws JsonException
     */
    public function testItHasValidationErrorWhenNewPasswordIsTheSame(): void
    {
        $admin = $this->loginAsAdmin();
        $admin->setPassword('Password1111');
        $admin->save();

        self::assertTrue(Hash::check('Password1111', $admin->password));

        $result = $this->postGraphQL(
            GraphQLQuery::mutation(self::MUTATION)
                ->args([
                    'current' => 'Password1111',
                    'password' => 'Password1111',
                    'passwordConfirmation' => 'Password1111',
                ])
                ->make()
        )
            ->assertOk();

        $this->assertResponseHasValidationMessage(
            $result,
            'password',
            [
                __('The password field and current must be different.'),
            ]
        );
    }
}
