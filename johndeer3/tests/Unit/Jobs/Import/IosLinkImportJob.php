<?php

namespace Tests\Unit\Jobs\Import;

use App\Jobs\MailSendJob;
use App\Models\Import\IosLinkImport;
use App\Notifications\SendLoginPassword;
use App\Notifications\SendResetPassword;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builder\UserBuilder;
use Tests\TestCase;
use Illuminate\Support\Facades\Notification;

class IosLinkImportJob extends TestCase
{
    use DatabaseTransactions;

    protected $userBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->userBuilder = resolve(UserBuilder::class);
    }

    /** @test */
    public function success_type_password(): void
    {
        $user = $this->userBuilder->create();
        $import = IosLinkImport::factory()->create(["user_id" => $user->id]);
//        $path

        $data = [
            "user" => $user,
            "password" => 'password',
            "type" => 'password',
        ];

        $job = new MailSendJob($data);
        $job->handle();

        Notification::assertSentTo([$user], SendLoginPassword::class);
    }

    /** @test */
    public function success_type_reset_password(): void
    {
        Notification::fake();

        $user = $this->userBuilder->create();

        $data = [
            "user" => $user,
            "password" => 'password',
            "type" => 'reset-password',
        ];

        $job = new MailSendJob($data);
        $job->handle();

        Notification::assertSentTo([$user], SendResetPassword::class);
    }

    /** @test */
    public function fail_without_user(): void
    {
        Notification::fake();

        $data = [
            "password" => 'password',
            "type" => 'reset-password',
        ];

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("There is no 'user' in the job data");

        (new MailSendJob($data))->handle();
    }

    /** @test */
    public function fail_without_type(): void
    {
        Notification::fake();

        $data = [
            "password" => 'password',
        ];

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("There is no 'type' in the job data");

        (new MailSendJob($data))->handle();
    }

    /** @test */
    public function fail_without_password(): void
    {
        Notification::fake();

        $user = $this->userBuilder->create();

        $data = [
            "user" => $user,
            "type" => 'reset-password',
        ];

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("There is no 'password' in the job data");

        (new MailSendJob($data))->handle();
    }

    /** @test */
    public function wrong_type(): void
    {
        Notification::fake();

        $user = $this->userBuilder->create();

        $data = [
            "user" => $user,
            "password" => 'password',
            "type" => 'wrong',
        ];

        $job = new MailSendJob($data);
        $job->handle();

        Notification::assertNotSentTo([$user], SendResetPassword::class);
    }
}
