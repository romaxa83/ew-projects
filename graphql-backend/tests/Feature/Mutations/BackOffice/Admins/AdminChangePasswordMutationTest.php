<?php

namespace Tests\Feature\Mutations\BackOffice\Admins;

use App\GraphQL\Mutations\BackOffice\Admins\AdminChangePasswordMutation;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminChangePasswordMutationTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = AdminChangePasswordMutation::NAME;

    public function test_it_change_password_success(): void
    {
        $admin = $this->loginAsAdmin();

        self::assertTrue(Hash::check('password', $admin->password));

        $query = sprintf(
            'mutation { %s ( current: "%s" password: "%s" password_confirmation: "%s" )}',
            self::MUTATION,
            'password',
            'new1password',
            'new1password'
        );

        $result = $this->postGraphQLBackOffice(['query' => $query])
            ->assertOk();

        [self::MUTATION => $data] = $result->json(['data']);

        self::assertTrue($data);

        $admin->refresh();

        self::assertTrue(Hash::check('new1password', $admin->password));
    }

    public function test_it_has_validation_error_when_old_password_is_not_correct(): void
    {
        $admin = $this->loginAsAdmin();

        self::assertTrue(Hash::check('password', $admin->password));

        $query = sprintf(
            'mutation { %s ( current: "%s" password: "%s" password_confirmation: "%s" )}',
            self::MUTATION,
            'password1',
            'new1password',
            'new1password'
        );

        $result = $this->postGraphQLBackOffice(['query' => $query])
            ->assertOk();

        $errors = $result->json('errors');

        $validation = array_shift($errors)['extensions']['validation']['current'];
        self::assertEquals(__('auth.password'), array_shift($validation));
    }

    public function test_it_has_error_for_not_auth_user(): void
    {
        $query = sprintf(
            'mutation { %s ( current: "%s" password: "%s" password_confirmation: "%s" )}',
            self::MUTATION,
            'password1',
            'new1password',
            'new1password'
        );

        $result = $this->postGraphQLBackOffice(['query' => $query])
            ->assertOk();

        $errors = $result->json('errors');

        self::assertEquals('Unauthorized', array_shift($errors)['message']);
    }

    public function test_it_has_validation_error_when_bad_password_confirmation(): void
    {
        $admin = $this->loginAsAdmin();

        self::assertTrue(Hash::check('password', $admin->password));

        $query = sprintf(
            'mutation { %s ( current: "%s" password: "%s" password_confirmation: "%s" )}',
            self::MUTATION,
            'password',
            'new1password',
            'new1password1'
        );

        $result = $this->postGraphQLBackOffice(['query' => $query])
            ->assertOk();

        $errors = $result->json('errors');

        $validation = array_shift($errors)['extensions']['validation']['password'];
        self::assertEquals(
            __('validation.confirmed', ['attribute' => 'password']),
            array_shift($validation)
        );
    }
}
