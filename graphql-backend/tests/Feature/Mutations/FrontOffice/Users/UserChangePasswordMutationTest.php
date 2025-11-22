<?php

namespace Tests\Feature\Mutations\FrontOffice\Users;

use App\GraphQL\Mutations\FrontOffice\Users\UserChangePasswordMutation;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserChangePasswordMutationTest extends TestCase
{
    use DatabaseTransactions;

    public function test_it_change_password_success(): void
    {
        $user = $this->loginAsUser();

        self::assertTrue(Hash::check('password', $user->password));

        $query = sprintf(
            'mutation { %s ( current: "%s" password: "%s" password_confirmation: "%s" )}',
            UserChangePasswordMutation::NAME,
            'password',
            'new1password',
            'new1password'
        );

        $result = $this->postGraphQL(['query' => $query])
            ->assertOk();

        [UserChangePasswordMutation::NAME => $data] = $result->json(['data']);

        self::assertTrue($data);

        $user->refresh();

        self::assertTrue(Hash::check('new1password', $user->password));
    }

    public function test_it_has_validation_error_when_old_password_is_not_correct(): void
    {
        $user = $this->loginAsUser();

        self::assertTrue(Hash::check('password', $user->password));

        $query = sprintf(
            'mutation { %s ( current: "%s" password: "%s" password_confirmation: "%s" )}',
            UserChangePasswordMutation::NAME,
            'password1',
            'new_password',
            'new_password'
        );

        $result = $this->postGraphQL(['query' => $query])
            ->assertOk();

        $errors = $result->json('errors');

        $validation = array_shift($errors)['extensions']['validation']['current'];
        self::assertEquals(__('auth.password', ['attribute' => 'password']), array_shift($validation));
    }

    public function test_it_has_error_for_not_auth_user(): void
    {
        $query = sprintf(
            'mutation { %s ( current: "%s" password: "%s" password_confirmation: "%s" )}',
            UserChangePasswordMutation::NAME,
            'password1',
            'new_password',
            'new_password'
        );

        $result = $this->postGraphQL(['query' => $query])
            ->assertOk();

        $errors = $result->json('errors');

        self::assertEquals('Unauthorized', array_shift($errors)['message']);
    }

    public function test_it_has_validation_error_when_bad_password_confirmation(): void
    {
        $user = $this->loginAsUser();

        self::assertTrue(Hash::check('password', $user->password));

        $query = sprintf(
            'mutation { %s ( current: "%s" password: "%s" password_confirmation: "%s" )}',
            UserChangePasswordMutation::NAME,
            'password',
            'new1password',
            'new1password1'
        );

        $result = $this->postGraphQL(['query' => $query])
            ->assertOk();

        $errors = $result->json('errors');

        $validation = array_shift($errors)['extensions']['validation']['password'];
        self::assertEquals(__('validation.confirmed', ['attribute' => 'password']), array_shift($validation));
    }
}
