<?php

namespace Tests\Unit\Models\Communications\CommunicationRecord;

use App\Models\Client\Activity;
use App\Models\Communications\CommunicationRecord;
use App\Models\Mailbox\Gmail\Message;
use App\Models\Twilio\TwilioSms;
use App\Models\Zadarma\CallsEvents;
use App\Models\Zadarma\SmsEvents;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Clients\ActivityBuilder;
use Tests\Builders\Communications\CommunicationRecordBuilder;
use Tests\Builders\Gmail\MessageBuilder;
use Tests\Builders\Twilio\TwilioSmsBuilder;
use Tests\Builders\Zadarma\CallEventBuilder;
use Tests\Builders\Zadarma\SmsEventBuilder;
use Tests\TestCase;
use Tests\Builders\Ringostat;

class CommunicationRecordTest extends TestCase
{
    use DatabaseTransactions;

    protected CommunicationRecordBuilder $communicationRecordBuilder;
    protected TwilioSmsBuilder $twilioSmsBuilder;
    protected ActivityBuilder $activityBuilder;
    protected Ringostat\EventAfterCallBuilder $ringostatCallBuilder;

    protected CallEventBuilder $zadarmaCallBuilder;
    protected SmsEventBuilder $smsBuilder;
    protected MessageBuilder $gmailMessageBuilder;

    public function setUp(): void
    {
        $this->communicationRecordBuilder = resolve(CommunicationRecordBuilder::class);
        $this->twilioSmsBuilder = resolve(TwilioSmsBuilder::class);
        $this->activityBuilder = resolve(ActivityBuilder::class);
        $this->ringostatCallBuilder = resolve(Ringostat\EventAfterCallBuilder::class);
        $this->zadarmaCallBuilder = resolve(CallEventBuilder::class);
        $this->smsBuilder = resolve(SmsEventBuilder::class);
        $this->gmailMessageBuilder = resolve(MessageBuilder::class);


        parent::setUp();
    }

    /** @test */
    public function isTwilio()
    {
        /** @var $entity TwilioSms */
        $entity = $this->twilioSmsBuilder->create();

        /** @var $model CommunicationRecord */
        $model = $this->communicationRecordBuilder
            ->entity($entity)
            ->create();

        $this->assertFalse($model->isZadarmaSms());
        $this->assertFalse($model->isZadarmaCall());
        $this->assertTrue($model->isTwilioSms());
        $this->assertFalse($model->isRingostatCall());
        $this->assertFalse($model->isClientActivity());
        $this->assertFalse($model->isGmailMsg());
    }

    /** @test */
    public function isClientActivity()
    {
        /** @var $entity Activity */
        $entity = $this->activityBuilder->create();

        /** @var $model CommunicationRecord */
        $model = $this->communicationRecordBuilder
            ->entity($entity)
            ->create();

        $this->assertFalse($model->isZadarmaSms());
        $this->assertFalse($model->isZadarmaCall());
        $this->assertFalse($model->isTwilioSms());
        $this->assertFalse($model->isRingostatCall());
        $this->assertTrue($model->isClientActivity());
        $this->assertFalse($model->isGmailMsg());
    }

    /** @test */
    public function isRingostatCall()
    {
        /** @var $entity Activity */
        $entity = $this->ringostatCallBuilder->create();

        /** @var $model CommunicationRecord */
        $model = $this->communicationRecordBuilder
            ->entity($entity)
            ->create();

        $this->assertFalse($model->isZadarmaSms());
        $this->assertFalse($model->isZadarmaCall());
        $this->assertFalse($model->isTwilioSms());
        $this->assertTrue($model->isRingostatCall());
        $this->assertFalse($model->isClientActivity());
        $this->assertFalse($model->isGmailMsg());
    }

    /** @test */
    public function isZadarmaCall()
    {
        /** @var $entity CallsEvents */
        $entity = $this->zadarmaCallBuilder->create();

        /** @var $model CommunicationRecord */
        $model = $this->communicationRecordBuilder
            ->entity($entity)
            ->create();

        $this->assertFalse($model->isZadarmaSms());
        $this->assertTrue($model->isZadarmaCall());
        $this->assertFalse($model->isTwilioSms());
        $this->assertFalse($model->isRingostatCall());
        $this->assertFalse($model->isClientActivity());
        $this->assertFalse($model->isGmailMsg());
    }

    /** @test */
    public function isZadarmaSms()
    {
        /** @var $entity SmsEvents */
        $entity = $this->smsBuilder->create();

        /** @var $model CommunicationRecord */
        $model = $this->communicationRecordBuilder
            ->entity($entity)
            ->create();

        $this->assertTrue($model->isZadarmaSms());
        $this->assertFalse($model->isZadarmaCall());
        $this->assertFalse($model->isTwilioSms());
        $this->assertFalse($model->isRingostatCall());
        $this->assertFalse($model->isClientActivity());
        $this->assertFalse($model->isGmailMsg());
    }

    /** @test */
    public function isGmailMsg()
    {
        /** @var $entity Message */
        $entity = $this->gmailMessageBuilder->create();

        /** @var $model CommunicationRecord */
        $model = $this->communicationRecordBuilder
            ->entity($entity)
            ->create();

        $this->assertTrue($model->isGmailMsg());
        $this->assertFalse($model->isZadarmaSms());
        $this->assertFalse($model->isZadarmaCall());
        $this->assertFalse($model->isTwilioSms());
        $this->assertFalse($model->isRingostatCall());
        $this->assertFalse($model->isClientActivity());
    }
}
