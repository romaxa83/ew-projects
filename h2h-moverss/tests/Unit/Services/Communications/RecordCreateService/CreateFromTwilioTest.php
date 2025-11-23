<?php

namespace Tests\Unit\Services\Communications\RecordCreateService;

use App\Enums\Communications\Type;
use App\Models\Communications\CommunicationRecord;
use App\Models\Division;
use App\Models\Twilio\TwilioSms;
use App\Services\Communications\RecordCreateService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Clients\ClientBuilder;
use Tests\Builders\Clients\PhoneBuilder;
use Tests\Builders\Communications\CommunicationRecordBuilder;
use Tests\Builders\Divisions\DivisionBuilder;
use Tests\Builders\Twilio\TwilioSmsBuilder;
use Tests\TestCase;
use Tests\Builders\Ringostat;

class CreateFromTwilioTest extends TestCase
{
    use DatabaseTransactions;

    protected TwilioSmsBuilder $twilioSmsBuilder;
    protected Ringostat\EventAfterCallBuilder $callBuilder;
    protected DivisionBuilder $divisionBuilder;

    protected ClientBuilder $clientBuilder;
    protected PhoneBuilder $clientPhoneBuilder;
    protected CommunicationRecordBuilder $communicationRecordBuilder;

    public function setUp(): void
    {
        $this->twilioSmsBuilder = resolve(TwilioSmsBuilder::class);
        $this->divisionBuilder = resolve(DivisionBuilder::class);
        $this->clientBuilder = resolve(ClientBuilder::class);
        $this->clientPhoneBuilder = resolve(PhoneBuilder::class);
        $this->communicationRecordBuilder = resolve(CommunicationRecordBuilder::class);
        $this->callBuilder = resolve(Ringostat\EventAfterCallBuilder::class);

        parent::setUp();
    }

    /** @test */
    public function create()
    {
        /** @var $division Division */
        $division = $this->divisionBuilder->create();

        /** @var $model TwilioSms */
        $model = $this->twilioSmsBuilder
            ->division($division)
            ->direction('inbound')
            ->from('+1999999999')
            ->to('+1222222222')
            ->create();

        $this->assertEquals(0, CommunicationRecord::count());

        RecordCreateService::handler($model);

        /** @var $rec CommunicationRecord */
        $rec = CommunicationRecord::first();

        $this->assertNull($rec->client_id);
        $this->assertEquals($rec->division_id, $division->id);
        $this->assertEquals($rec->type, Type::Inbound);
        $this->assertEquals($rec->channel_contact, '999999999');
        $this->assertEquals($rec->sort_at, $model->created_at);

        $this->assertFalse($rec->is_answered);

        $this->assertInstanceOf(TwilioSms::class, $rec->entity);
        $this->assertEquals($rec->entity->id, $model->id);
    }

    /** @test */
    public function create_check_outbound_and_assert_not_answered_another_recs()
    {
        /** @var $division Division */
        $division = $this->divisionBuilder->create();

        $now = CarbonImmutable::now();

        $targetChannelContact = '6455764654';

        $twilio_1 = $this->twilioSmsBuilder
            ->division($division)
            ->create();
        $twilio_2 = $this->twilioSmsBuilder
            ->division($division)
            ->create();
        $ringostat_1 = $this->callBuilder
            ->create();
        $ringostat_2 = $this->callBuilder
            ->create();

        $rec_1 = $this->communicationRecordBuilder
            ->entity($twilio_1)
            ->channel_contact($targetChannelContact)
            ->type(Type::Inbound)
            ->is_answered(false)
            ->sort_at($now->subDay())
            ->create();
        $rec_2 = $this->communicationRecordBuilder
            ->entity($twilio_2)
            ->channel_contact('333333')
            ->type(Type::Inbound)
            ->is_answered(false)
            ->sort_at($now->subDay())
            ->create();
        $rec_3 = $this->communicationRecordBuilder
            ->entity($ringostat_1)
            ->channel_contact($targetChannelContact)
            ->type(Type::Inbound)
            ->is_answered(false)
            ->sort_at($now->subDay())
            ->create();
        $rec_4 = $this->communicationRecordBuilder
            ->entity($ringostat_2)
            ->channel_contact('3333333')
            ->type(Type::Inbound)
            ->is_answered(false)
            ->sort_at($now->subDay())
            ->create();


        /** @var $model TwilioSms */
        $model = $this->twilioSmsBuilder
            ->division($division)
            ->direction('outbound-api')
            ->from('+1999999999')
            ->to('+1'.$targetChannelContact)
            ->create();

        $this->assertEquals(4, CommunicationRecord::count());

        RecordCreateService::handler($model);

        $this->assertEquals(5, CommunicationRecord::count());

        /** @var $rec CommunicationRecord */
        $rec = CommunicationRecord::query()->latest('sort_at')->first();

        $this->assertNull($rec->client_id);
        $this->assertEquals($rec->division_id, $division->id);
        $this->assertEquals($rec->type, Type::Outbound);
        $this->assertEquals($rec->channel_contact, $targetChannelContact);

        $this->assertTrue($rec->is_answered);

        $this->assertInstanceOf(TwilioSms::class, $rec->entity);
        $this->assertEquals($rec->entity->id, $model->id);

        $rec_1->refresh();
        $rec_2->refresh();
        $rec_3->refresh();
        $rec_4->refresh();

        $this->assertTrue($rec_1->is_answered);
        $this->assertFalse($rec_2->is_answered);
        $this->assertTrue($rec_3->is_answered);
        $this->assertFalse($rec_4->is_answered);
    }

    /** @test */
    public function check_detect_client()
    {
        /** @var $division Division */
        $division = $this->divisionBuilder->create();

        $phoneValue = '999999999';
        $client = $this->clientBuilder->create();
        $this->clientPhoneBuilder
            ->client($client)
            ->value($phoneValue)
            ->create();

        /** @var $model TwilioSms */
        $model = $this->twilioSmsBuilder
            ->division($division)
            ->direction('inbound')
            ->from('+1'.$phoneValue)
            ->create();

        $this->assertEquals(0, CommunicationRecord::count());

        RecordCreateService::handler($model);

        /** @var $rec CommunicationRecord */
        $rec = CommunicationRecord::first();

        $this->assertEquals($rec->client_id, $client->id);
        $this->assertNull($rec->client_ids);
    }

    /** @test */
    public function check_detect_clients()
    {
        /** @var $division Division */
        $division = $this->divisionBuilder->create();

        $phoneValue = '999999999';
        $client_1 = $this->clientBuilder->create();
        $client_2 = $this->clientBuilder->create();
        $client_3 = $this->clientBuilder->create();
        $this->clientPhoneBuilder
            ->client($client_1)
            ->value($phoneValue)
            ->create();
        $this->clientPhoneBuilder
            ->client($client_2)
            ->value($phoneValue)
            ->create();
        $this->clientPhoneBuilder
            ->client($client_3)
            ->value($phoneValue)
            ->create();

        /** @var $model TwilioSms */
        $model = $this->twilioSmsBuilder
            ->division($division)
            ->direction('inbound')
            ->from('+1'.$phoneValue)
            ->create();

        $this->assertEquals(0, CommunicationRecord::count());

        RecordCreateService::handler($model);

        /** @var $rec CommunicationRecord */
        $rec = CommunicationRecord::first();

        $this->assertEquals($rec->client_id, $client_1->id);
        $this->assertCount(2, $rec->client_ids);
        $this->assertEquals($rec->client_ids, [
            $client_2->id,
            $client_3->id,
        ]);
    }
}
