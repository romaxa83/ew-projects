<?php


namespace Services\Users;

use App\Models\History\UserHistory;
use App\Models\Users\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\TestCase;

class UserHistoryServiceTest extends TestCase
{
    use DatabaseTransactions;

    public function test_history_create_user(): void
    {
        $this->loginAsCarrierSuperAdmin();

        $attributes = [
            'full_name' => 'Full Name',
            'phone' => '1-541-754-3010',
            'email' => $this->faker->email,
        ];

        $driverInfoAttributes = [
            'driver_license_number' => $this->faker->randomLetter,
        ];

        $driversRole = $this->getRoleRepository()->findByName(User::DRIVER_ROLE);

        $roles = [
            'role_id' => $driversRole->id,
            'owner_id' => $this->authenticatedUser->id,
        ];

        $this->assertDatabaseMissing(
            UserHistory::TABLE_NAME,
            [
                'performer_id' => $this->authenticatedUser->id,
            ]
        );

        $response = $this->postJson(
            route('users.store'),
            $attributes + $driverInfoAttributes + $roles
        )
            ->assertCreated();

        $driver = $response->json('data');

        $this->assertDatabaseHas(
            UserHistory::TABLE_NAME,
            [
                'performer_id' => $this->authenticatedUser->id,
                'user_id' => $driver['id'],
                //'operation' => 'user_histories.user_created',
                'operation' => UserHistory::STATUS_CREATED,
            ]
        );
    }

    public function test_user_history_deactivate_delete(): void
    {
        $this->loginAsCarrierSuperAdmin();

        $attributes = [
            'full_name' => 'Full Name',
            'phone' => '1-541-754-3010',
            'email' => $this->faker->email,
        ];

        $driverInfoAttributes = [
            'driver_license_number' => $this->faker->randomLetter,
        ];

        $driversRole = $this->getRoleRepository()->findByName(User::DRIVER_ROLE);

        $roles = [
            'role_id' => $driversRole->id,
            'owner_id' => $this->authenticatedUser->id,
        ];

        $response = $this->postJson(
            route('users.store'),
            $attributes + $driverInfoAttributes + $roles
        )
            ->assertCreated();

        $driver = $response->json('data');

        $this->assertDatabaseHas(
            UserHistory::TABLE_NAME,
            [
                'performer_id' => $this->authenticatedUser->id,
                'user_id' => $driver['id'],
                //'operation' => 'user_histories.user_created',
                'operation' => UserHistory::STATUS_CREATED,
            ]
        );

        $user = User::find($driver['id']);
        $user->status = User::STATUS_ACTIVE;
        $user->save();

        $this->putJson(route('users.change-status', $driver['id']))
            ->assertOk();

        $this->assertDatabaseHas(
            UserHistory::TABLE_NAME,
            [
                'performer_id' => $this->authenticatedUser->id,
                'user_id' => $driver['id'],
                'operation' => UserHistory::STATUS_DEACTIVATED,
            ]
        );

        /*$this->deleteJson(route('users.destroy', $driver['id']))
            ->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertDatabaseHas(
            UserHistory::TABLE_NAME,
            [
                'performer_id' => $this->authenticatedUser->id,
                'user_id' => $driver['id'],
                'operation' => 'user_histories.user_deleted',
            ]
        );*/
    }
}
