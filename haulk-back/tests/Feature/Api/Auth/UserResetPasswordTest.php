<?php


namespace Tests\Feature\Api\Auth;


use App\Models\Users\User;
use Exception;
use Hash;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class UserResetPasswordTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    /**
     * @throws Exception
     */
    public function test_it_reset_password_success()
    {
        $attributes = [
            'email' => $this->faker->unique()->email,
            'password' => 'password123',
        ];

        /** @var User $user */
        $user = User::factory()->create($attributes + ['status' => User::STATUS_ACTIVE]);
        $user->assignRole(User::SUPERADMIN_ROLE);

        $token = Password::createToken($user);
        $newAttributes = [
            'token' => $token,
            'email' => $user->email,
            'password' => 'password321',
            'password_confirmation' => 'password321'
        ];
        $this->postJson(route('password.reset'), $newAttributes)
            ->assertOk();
        $this->assertFalse(
            Hash::check(
                $attributes['password'],
                $user->fresh()->password
            )
        );
        $this->assertTrue(Hash::check('password321', $user->fresh()->password));
    }

    public function test_it_cant_forgot_password()
    {
        $attributes = [
            'email' => 'email@example.com',
            'password' => '1234567',
        ];

        $this->postJson(route('password.reset'), $attributes)
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function test_it_reset_password_success_for_pending_user()
    {
        $attributes = [
            'email' => $this->faker->unique()->email,
            'password' => 'password123',
        ];

        /** @var User $user */
        $user = User::factory()->create($attributes + ['status' => User::STATUS_PENDING]);
        $user->assignRole(User::SUPERADMIN_ROLE);

        $token = Password::createToken($user);
        $newAttributes = [
            'token' => $token,
            'email' => $user->email,
            'password' => 'password321',
            'password_confirmation' => 'password321'
        ];
        $this->postJson(route('password.reset'), $newAttributes)
            ->assertOk();
        $this->assertFalse(
            Hash::check(
                $attributes['password'],
                $user->fresh()->password
            )
        );
        $this->assertTrue(Hash::check('password321', $user->fresh()->password));
        $user->refresh();
        $this->assertEquals(User::STATUS_ACTIVE, $user->status);
    }

    /**
     * @throws Exception
     */
    public function test_it_reset_password_success_for_ds_users()
    {
        $attributes = [
            'email' => $this->faker->unique()->email,
            'password' => 'password123',
            'carrier_id' => null,
        ];

        /** @var User $user */
        $user = User::factory()->create($attributes + ['status' => User::STATUS_ACTIVE]);
        $user->assignRole(User::BSSUPERADMIN_ROLE);

        $token = Password::createToken($user);
        $newAttributes = [
            'token' => $token,
            'email' => $user->email,
            'password' => 'password321',
            'password_confirmation' => 'password321'
        ];
        $this->postJson(route('password.reset'), $newAttributes)
            ->assertOk();
        $this->assertFalse(
            Hash::check(
                $attributes['password'],
                $user->fresh()->password
            )
        );
        $this->assertTrue(Hash::check('password321', $user->fresh()->password));
    }
}
