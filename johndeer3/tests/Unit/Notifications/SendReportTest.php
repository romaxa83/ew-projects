<?php

namespace Tests\Unit\Notifications;

use App\Models\User\User;
use App\Notifications\SendReport;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Builder\UserBuilder;

class SendReportTest extends TestCase
{
    use DatabaseTransactions;

    protected $userBuilder;
    protected $reportBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->userBuilder = resolve(UserBuilder::class);
    }

    /** @test */
    public function success_to_mail(): void
    {
        $link = "https://google.com/androide.app";

        /** @var $model User */
        $user = $this->userBuilder->profileData([
            "first_name" => "cubic",
            "last_name" => "rubic",
        ])
            ->create();

        $notification = new SendReport($link);
        $message = $notification->toMail($user);

        $this->assertEquals(
            "You report",
            $message->subject
        );
        $this->assertEquals(
            "Report",
            $message->greeting
        );
        $this->assertEquals(
            'Thank you for using our service!',
            $message->outroLines[0]
        );
        $this->assertEquals(
            'Link for report',
            $message->actionText
        );
        $this->assertEquals(
            $link,
            $message->actionUrl
        );
        $this->assertEquals(
            $link,
            $message->attachments[0]["file"]
        );
    }

    /** @test */
    public function success_via(): void
    {
        /** @var $model User */
        $user = $this->userBuilder->create();

        $link = "https://google.com/androide.app";
        $notification = new SendReport($link);
        $message = $notification->via($user);

        $this->assertEquals($message, ['mail']);
    }

    /** @test */
    public function success_to_array(): void
    {
        /** @var $model User */
        $user = $this->userBuilder->create();
        $link = "https://google.com/androide.app";
        $notification = new SendReport($link);
        $message = $notification->toArray($user);

        $this->assertEmpty($message);
    }
}
