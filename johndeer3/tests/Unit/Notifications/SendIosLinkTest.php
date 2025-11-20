<?php

namespace Tests\Unit\Notifications;

use App\Models\User\IosLink;
use App\Models\User\User;
use App\Notifications\SendIosLink;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Notifications\Action;
use Tests\TestCase;
use Tests\Builder\UserBuilder;

class SendIosLinkTest extends TestCase
{
    use DatabaseTransactions;

    protected $userBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->userBuilder = resolve(UserBuilder::class);
    }

    /** @test */
    public function success_to_mail(): void
    {
        $linkAndroid = "https://google.com/androide.app";
        \Config::set('app.android_link', $linkAndroid);

        $link = IosLink::factory()->create();
        $password = User::DEFAULT_PASSWORD;

        /** @var $model User */
        $user = $this->userBuilder->profileData([
            "first_name" => "cubic",
            "last_name" => "rubic",
        ])
            ->setPassword($password)
            ->setIosLink($link)
            ->create();

        $notification = new SendIosLink($user);
        $message = $notification->toMail($user);

        $nameApp = prettyAppName();

        $this->assertEquals(
            "Your login and password to enter the application {$nameApp}",
            $message->subject
        );
        $this->assertEquals(
            "Hello, {$user->fullName()}",
            $message->greeting
        );
        $this->assertEquals(
            "<strong>Your login</strong> - {$user->login}",
            $message->introLines[0]
        );
        $this->assertEquals(
            "<strong>Your password</strong> - use your password",
            $message->introLines[1]
        );
        $this->assertEquals(
            new Action('Go to download android app', $linkAndroid),
            $message->outroLines[0]->action
        );
        $this->assertEquals(
            'Thank you for using our service!',
            $message->outroLines[1]
        );
        $this->assertEquals(
            'Go to download ios app',
            $message->actionText
        );
        $this->assertEquals(
            $link->link,
            $message->actionUrl
        );
    }

    /** @test */
    public function success_via(): void
    {
        /** @var $model User */
        $user = $this->userBuilder->create();

        $notification = new SendIosLink($user);
        $message = $notification->via($user);

        $this->assertEquals($message, ['mail']);
    }

    /** @test */
    public function success_to_array(): void
    {
        /** @var $model User */
        $user = $this->userBuilder->create();

        $notification = new SendIosLink($user);
        $message = $notification->toArray($user);

        $this->assertEmpty($message);
    }
}

