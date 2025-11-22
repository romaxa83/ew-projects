<?php

namespace Tests\Unit\Notifications\Dealers;

use App\Dto\Dealers\DealerDto;
use App\Notifications\Dealers\SendCredentialsNotification;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class SendCredentialsNotificationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function success_send()
    {
        $dto = DealerDto::byArgs([
            'email' => $this->faker->safeEmail,
            'name' => $this->faker->name,
            'company_id' => 1,
        ]);
        $dto->companyName = 'company name';

        $notifications = new SendCredentialsNotification($dto);
        $msg = $notifications->toMail($dto);

        $this->assertEquals($msg->subject, __('messages.dealer.send_credentials.subject'));
        $this->assertEquals($msg->introLines[0], __('messages.dealer.send_credentials.greeting',[
            'name' => $dto->name
        ]));
        $this->assertEquals($msg->introLines[2], __('messages.dealer.send_credentials.line_1',[
            'company_name' => $dto->companyName
        ]));
        $this->assertEquals($msg->introLines[4], __('messages.dealer.send_credentials.line_2'));
        $this->assertEquals($msg->introLines[5], $dto->email->getValue());
        $this->assertEquals($msg->introLines[6], __('messages.dealer.send_credentials.line_3'));
        $this->assertEquals($msg->introLines[7], $dto->password);

        $this->assertEquals($msg->outroLines[1], __('messages.dealer.send_credentials.line_4'));

        $this->assertEquals($msg->actionText, "Login");
        $this->assertEquals($msg->actionUrl, config('app.site_url') . "/?isLogin=true");
    }

    /** @test */
    public function send_without_company_name()
    {
        $dto = DealerDto::byArgs([
            'email' => $this->faker->safeEmail,
            'name' => $this->faker->name,
            'company_id' => 1,
        ]);

        $notifications = new SendCredentialsNotification($dto);
        $msg = $notifications->toMail($dto);

        $this->assertEquals($msg->introLines[2], __('messages.dealer.send_credentials.line_1',[
            'company_name' => null
        ]));
    }
}
