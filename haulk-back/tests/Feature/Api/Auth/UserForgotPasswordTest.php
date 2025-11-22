<?php


namespace Tests\Feature\Api\Auth;


use App\Models\Users\User;
use Exception;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Tests\TestCase;

class UserForgotPasswordTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    /**
     * @throws Exception
     */
    public function test_it_forgot_password_success()
    {
        $attributes = [
            'email' => $this->faker->unique()->email,
            'password' => $this->faker->password(10),
        ];

        /** @var User $user */
        $user = User::factory()->create($attributes + ['status' => User::STATUS_ACTIVE]);
        $user->assignRole(User::SUPERADMIN_ROLE);

        $this->postJson(route('password.forgot'), $attributes)
            ->assertOk();
    }

    public function test_it_cant_forgot_password()
    {
        $attributes = [
            'email' => 'email@example.com',
            'password' => '1234567',
        ];

        $this->postJson(route('password.forgot'), $attributes)
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function test_it_forgot_password_success_for_bs_users()
    {
        $attributes = [
            'email' => $this->faker->unique()->email,
            'password' => $this->faker->password(10),
            'carrier_id' => null,
        ];

        /** @var User $user */
        $user = User::factory()->create($attributes + ['status' => User::STATUS_ACTIVE]);
        $user->assignRole(User::BSADMIN_ROLE);

        $this->postJson(route('password.forgot'), $attributes)
            ->assertOk();
    }
}
