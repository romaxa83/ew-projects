<?php

namespace Tests\Feature\Http\Api\V1\Users\UserAction;

use App\Enums\Users\UserStatus;
use App\Models\Users\User;
use App\Notifications\Auth\ConfirmRegistrationNotification;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Notification;
use Tests\Builders\Users\UserBuilder;
use Tests\TestCase;

class ResendInvitationLinkTest extends TestCase
{
    use DatabaseTransactions;

    protected UserBuilder $userBuilder;

    public function setUp(): void
    {
        parent::setUp();

        $this->userBuilder = resolve(UserBuilder::class);
    }

    /** @test */
    public function success_resend()
    {
        $this->loginUserAsSuperAdmin();

        Notification::fake();

        /** @var $model User */
        $model = $this->userBuilder->status(UserStatus::PENDING())->asAdmin()->create();

        $res = $this->putJson(route('api.v1.users.resend-invitation-link', ['id' => $model->id]))
        ;

        $this->assertSuccessMsg($res);

        $this->assertNotificationSentTo(
            $model->email->getValue(),
            ConfirmRegistrationNotification::class
        );
    }

    /** @test */
    public function fail_user_is_not_pending()
    {
        $this->loginUserAsSuperAdmin();

        Notification::fake();

        /** @var $model User */
        $model = $this->userBuilder->asAdmin()->create();

        $res = $this->putJson(route('api.v1.users.resend-invitation-link', ['id' => $model->id]));

        self::assertErrorMsg($res, __('exceptions.user.not_pending_status'), Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertNotificationNotSentTo(
            $model->email->getValue(),
            ConfirmRegistrationNotification::class
        );
    }

    /** @test */
    public function fail_not_found()
    {
        $this->loginUserAsSuperAdmin();

        $res = $this->putJson(route('api.v1.users.resend-invitation-link', ['id' => 0]));

        self::assertErrorMsg($res, __("exceptions.user.not_found"), Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function not_perm_manipulate_for_this_role()
    {
        $this->loginUserAsAdmin();

        /** @var $model User */
        $model = $this->userBuilder->status(UserStatus::PENDING())->asSuperAdmin()->create();

        $res = $this->putJson(route('api.v1.users.resend-invitation-link', ['id' => $model->id]))
        ;

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_perm()
    {
        $this->loginUserAsMechanic();

        /** @var $model User */
        $model = $this->userBuilder->status(UserStatus::PENDING())->asAdmin()->create();

        $res = $this->putJson(route('api.v1.users.resend-invitation-link', ['id' => $model->id]));

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        /** @var $model User */
        $model = $this->userBuilder->status(UserStatus::PENDING())->asAdmin()->create();

        $res = $this->putJson(route('api.v1.users.resend-invitation-link', ['id' => $model->id]));

        self::assertUnauthenticatedMessage($res);
    }
}
