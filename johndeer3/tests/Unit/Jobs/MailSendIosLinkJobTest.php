<?php

namespace Tests\Unit\Jobs;

use App\Jobs\MailSendIosLinkJob;
use App\Notifications\SendIosLink;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builder\UserBuilder;
use Tests\TestCase;
use Illuminate\Support\Facades\Notification;

class MailSendIosLinkJobTest extends TestCase
{
    use DatabaseTransactions;

    protected $userBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->userBuilder = resolve(UserBuilder::class);
    }

    /** @test */
    public function success(): void
    {
        Notification::fake();

        $user = $this->userBuilder->create();

        $data = [
            "user" => $user
        ];

        $job = new MailSendIosLinkJob($data);
        $job->handle();

        $this->assertEquals(md5($user), md5($job->getUser()));

        Notification::assertSentTo([$user], SendIosLink::class);
    }

    /** @test */
    public function success_check_user(): void
    {
        Notification::fake();

        $data = [];

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("There is no 'user' in the job data");

        new MailSendIosLinkJob($data);
    }
}

