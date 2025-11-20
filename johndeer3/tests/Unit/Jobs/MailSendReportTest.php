<?php

namespace Tests\Unit\Jobs;

use App\Jobs\MailSendReport;
use App\Notifications\SendReport;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Notifications\AnonymousNotifiable;
use Tests\TestCase;
use Illuminate\Support\Facades\Notification;

class MailSendReportTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function success(): void
    {
        Notification::fake();

        $data = [
            "email" => 'test@test.com',
            "link" => 'some link',
        ];

        $job = new MailSendReport($data);
        $job->handle();

        Notification::assertSentTo(new AnonymousNotifiable(), SendReport::class,
            function ($notification, $channels, $notifiable) use ($data) {
                return $notifiable->routes['mail'] == $data['email']
                    && $channels[0] == 'mail';
            }
        );
    }

    /** @test */
    public function fail_without_email(): void
    {
        Notification::fake();

        $data = ["link" => 'some link'];

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("There is no email address or link in the job data");

        (new MailSendReport($data))->handle();

    }

    /** @test */
    public function fail_without_link(): void
    {
        Notification::fake();

        $data = ["email" => 'test@test.com'];

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("There is no email address or link in the job data");

        (new MailSendReport($data))->handle();

    }
}


