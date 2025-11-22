<?php

namespace Tests\Feature\Http\Api\V1\Users\UserCrud;

use App\Enums\Users\UserStatus;
use App\Models\Users\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\Builders\Users\UserBuilder;
use Tests\TestCase;

class DeleteTest extends TestCase
{
    use DatabaseTransactions;

    protected UserBuilder $userBuilder;

    public function setUp(): void
    {
        parent::setUp();

        $this->userBuilder = resolve(UserBuilder::class);
    }

    /** @test */
    public function success_delete()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $m_1 User */
        $m_1 = $this->userBuilder->status(UserStatus::ACTIVE())
            ->asAdmin()->create();

        $id = $m_1->id;

        $this->deleteJson(route('api.v1.users.delete', ['id' => $m_1->id]))
            ->assertNoContent()
        ;

        $this->assertTrue(User::query()->where('id', $id)->withTrashed()->exists());
    }

    /** @test */
    public function fail_not_found()
    {
        $this->loginUserAsSuperAdmin();

        $res = $this->deleteJson(route('api.v1.users.delete', ['id' => 0]));

        self::assertErrorMsg($res, __("exceptions.user.not_found"), Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function fail_delete_self()
    {
        $model = $this->loginUserAsAdmin();

        $res = $this->deleteJson(route('api.v1.users.delete', ['id' => $model->id]));

        self::assertForbiddenMessageAsReal($res);
    }

    /** @test */
    public function not_perm_manipulate_for_this_role()
    {
        $this->loginUserAsAdmin();

        $model = $this->userBuilder->asSuperAdmin()->create();

        $res = $this->deleteJson(route('api.v1.users.delete', ['id' => $model->id]));

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_perm()
    {
        $this->loginUserAsMechanic();

        /** @var $m_1 User */
        $m_1 = $this->userBuilder->asAdmin()->create();

        $res = $this->deleteJson(route('api.v1.users.delete', ['id' => $m_1->id]));

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        /** @var $m_1 User */
        $m_1 = $this->userBuilder->asAdmin()->create();

        $res = $this->deleteJson(route('api.v1.users.delete', ['id' => $m_1->id]));

        self::assertUnauthenticatedMessage($res);
    }
}

