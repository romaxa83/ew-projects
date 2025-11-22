<?php

namespace Tests\Feature\Http\Api\V1\Users\UserAction;

use App\Enums\Users\UserStatus;
use App\Models\Users\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\Builders\Users\UserBuilder;
use Tests\TestCase;

class ChangeStatusTest extends TestCase
{
    use DatabaseTransactions;

    protected UserBuilder $userBuilder;

    public function setUp(): void
    {
        parent::setUp();

        $this->userBuilder = resolve(UserBuilder::class);
    }

    /** @test */
    public function success_change_to_active()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model User */
        $model = $this->userBuilder->status(UserStatus::INACTIVE())->asAdmin()->create();

        $this->putJson(route('api.v1.users.change-status', ['id' => $model->id]))
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'status' => UserStatus::ACTIVE,
                ]
            ])
        ;
    }

    /** @test */
    public function success_change_to_inactive()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model User */
        $model = $this->userBuilder->status(UserStatus::ACTIVE())->asAdmin()->create();

        $this->putJson(route('api.v1.users.change-status', ['id' => $model->id]))
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'status' => UserStatus::INACTIVE,
                ]
            ])
        ;
    }

    /** @test */
    public function success_ignore_if_pending()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model User */
        $model = $this->userBuilder->status(UserStatus::PENDING())->asAdmin()->create();

        $this->putJson(route('api.v1.users.change-status', ['id' => $model->id]))
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'status' => UserStatus::PENDING,
                ]
            ])
        ;
    }

    /** @test */
    public function fail_not_found()
    {
        $this->loginUserAsSuperAdmin();

        $res = $this->putJson(route('api.v1.users.change-status', ['id' => 0]));

        self::assertErrorMsg($res, __("exceptions.user.not_found"), Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function not_perm_manipulate_for_this_role()
    {
        $this->loginUserAsAdmin();

        /** @var $model User */
        $model = $this->userBuilder->status(UserStatus::PENDING())->asSuperAdmin()->create();

        $res = $this->putJson(route('api.v1.users.change-status', ['id' => $model->id]));

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_perm()
    {
        $this->loginUserAsMechanic();

        /** @var $model User */
        $model = $this->userBuilder->status(UserStatus::PENDING())->asAdmin()->create();

        $res = $this->putJson(route('api.v1.users.change-status', ['id' => $model->id]));

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        /** @var $model User */
        $model = $this->userBuilder->status(UserStatus::PENDING())->asAdmin()->create();

        $res = $this->putJson(route('api.v1.users.change-status', ['id' => $model->id]));

        self::assertUnauthenticatedMessage($res);
    }
}

