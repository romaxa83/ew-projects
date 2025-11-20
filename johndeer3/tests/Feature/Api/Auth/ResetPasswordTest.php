<?php

namespace Tests\Feature\Api\Auth;

use App\Jobs\MailSendJob;
use App\Models\User\IosLink;
use App\Models\User\User;
use App\Notifications\SendResetPassword;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Notification;
use Tests\Builder\UserBuilder;
use Tests\TestCase;
use Illuminate\Http\Response;
use Tests\Traits\ResponseStructure;

class ResetPasswordTest extends TestCase
{
    use DatabaseTransactions;
    use ResponseStructure;

    protected $userBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
        $this->userBuilder = resolve(UserBuilder::class);
    }

    /** @test */
    public function success()
    {
        /** @var $user User */
        $password = 'password';
        $user = $this->userBuilder->setPassword($password)->create();
        $oldPassword = $user->password;

        $link = IosLink::factory()->create();

        \Queue::fake();
        Notification::fake();

        $this->assertNull($link->user_id);
        $this->assertEquals($link->status, 1);

        $this->postJson(route('api.reset-password'), [
            'email' => $user->email
        ])
            ->assertStatus(Response::HTTP_OK)
            ->assertJson($this->structureSuccessResponse(__('message.reset_password_success')))
        ;

        $link->refresh();
        $user->refresh();

        $this->assertEquals($link->user_id, $user->id);
        $this->assertEquals($link->status, 0);

        $this->assertNotEquals($user->password, $oldPassword);

        \Queue::assertPushed(MailSendJob::class, function ($job) {
            return $job->data['user'] instanceof User
                && $job->data['type'] == 'reset-password'
                && $job->data['password'] == User::generateRandomPassword()
                ;
        });

//        Notification::assertSentTo($user, SendResetPassword::class);
    }

    /** @test */
    public function fail_not_user_by_email()
    {
        $this->userBuilder->create();

        $this->postJson(route('api.reset-password'), [
            'email' => "wrong@email.net"
        ])
            ->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJson($this->structureErrorResponse(['The selected email is invalid.']))
        ;
    }

    /** @test */
    public function fail_without_email()
    {
        $this->userBuilder->create();

        $this->postJson(route('api.reset-password'))
            ->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJson($this->structureErrorResponse(['The email field is required.']))
        ;
    }

    /** @test */
    public function fail_not_ios_link()
    {
        /** @var $user User */
        $password = 'password';
        $user = $this->userBuilder->setPassword($password)->create();

        $this->postJson(route('api.reset-password'), [
            'email' => $user->email
        ])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJson($this->structureErrorResponse(__('message.not empty ios link')))
        ;
    }
}
