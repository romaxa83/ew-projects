<?php

namespace Tests\Feature\Twilio\Webhook;

use App\Http\Controllers\Twilio\TwilioWebhookController;
use App\Models\Communications\CommunicationRecord;
use App\Models\Division;
use App\Models\Ringostat\EventAfterCall;
use App\Models\Twilio\TwilioSms;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Divisions\DivisionBuilder;
use Tests\Builders\Ringostat;
use Tests\TestCase;
use Mockery;

class HandleSmsTest extends TestCase
{
    use DatabaseTransactions;
    protected DivisionBuilder $divisionBuilder;

    protected array $data;

    public function setUp(): void
    {
        $this->divisionBuilder = resolve(DivisionBuilder::class);

        parent::setUp();

        $this->data = [
            "MessageSid" => "10",
            "To" => "20",
            "From" => "inbound",
            "Body" => "10",
        ];
    }

    /** @test */
    public function create()
    {
//        \Config::set('app.twilio.token', 'twillio-token');
//
//        /** @var $division Division */
//        $division = $this->divisionBuilder->create();
//
//        $mock = Mockery::mock(TwilioWebhookController::class);
//        $mock->shouldReceive('detectDivision')
//            ->andReturn('ff');
//
//        $data = $this->data;

        $this->assertEquals(0, TwilioSms::count());
//        $this->assertEquals(0, CommunicationRecord::count());
//
//        $this->post(route('webhook.twilio.sms.handleSms'), $data,[
//            'X-Twilio-Signature' => 'twillio-token'
//        ])
//            ->dump()
//            ->assertJson([
//                'message' => "Event recorded successfully"
//            ])
//        ;

    }
}


