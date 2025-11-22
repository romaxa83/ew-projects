<?php

namespace Tests\Unit\Notifications\Companies;

use App\Models\Companies\Company;
use App\Notifications\Companies\SendCodeForDealerNotification;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Builders\Company\CompanyBuilder;
use Tests\TestCase;

class SendCodeForDealerNotificationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    protected CompanyBuilder $companyBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->companyBuilder = resolve(CompanyBuilder::class);
    }

    /** @test */
    public function success_send()
    {
        /** @var $model Company */
        $model = $this->companyBuilder->setData([
            'code' => '909090'
        ])->withManager()->create();

        $notifications = new SendCodeForDealerNotification($model);
        $msg = $notifications->toMail($model);

        $this->assertEquals($msg->subject, __('messages.company.send_code.subject'));
        $this->assertEquals($msg->introLines[0], __('messages.company.send_code.greeting',[
            'name' => $model->business_name
        ]));
        $this->assertEquals($msg->introLines[2], __('messages.company.send_code.line_1'));
        $this->assertEquals($msg->introLines[4], __('messages.company.send_code.line_2'));
        $this->assertEquals($msg->introLines[5], $model->manager->name);
        $this->assertEquals($msg->introLines[6], $model->manager->phone->getValue());
        $this->assertEquals($msg->introLines[7], $model->manager->email->getValue());
        $this->assertEquals($msg->introLines[9], __('messages.company.send_code.line_3', [
            'login' => $model->email->getValue()
        ]));
        $this->assertEquals($msg->introLines[10], __('messages.company.send_code.line_4', [
            'code' => $model->code
        ]));
        $this->assertEquals($msg->introLines[12], __('messages.company.send_code.line_5'));

        $this->assertEquals($msg->outroLines[1], __('messages.company.send_code.line_6'));

        $this->assertEquals($msg->actionText, 'Sing up');

        $this->assertEquals($msg->actionUrl, config('app.site_url') . '/?isRegister=true&email='.$model->email);
    }

    /** @test */
    public function send_without_manager()
    {
        /** @var $model Company */
        $model = $this->companyBuilder->setData([
            'code' => '909090'
        ])->create();

        $notifications = new SendCodeForDealerNotification($model);
        $msg = $notifications->toMail($model);

        $this->assertEquals($msg->introLines[5], "");
        $this->assertEquals($msg->introLines[6], "");
        $this->assertEquals($msg->introLines[7], "");
    }
}
