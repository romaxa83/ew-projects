<?php

namespace Tests\Unit\Services\Communications\RecordCreateService;

use App\Enums\Communications\Type;
use App\Models\Communications\CommunicationRecord;
use App\Models\Division;
use App\Models\Zadarma\SmsEvents;
use App\Services\Communications\RecordCreateService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Clients\ClientBuilder;
use Tests\Builders\Clients\PhoneBuilder;
use Tests\Builders\Divisions\DivisionBuilder;
use Tests\Builders\Zadarma\SmsEventBuilder;
use Tests\TestCase;

class CreateFromZadarmaSmsTest extends TestCase
{
    use DatabaseTransactions;

    protected SmsEventBuilder $smsBuilder;
    protected DivisionBuilder $divisionBuilder;
    protected ClientBuilder $clientBuilder;
    protected PhoneBuilder $clientPhoneBuilder;

    public function setUp(): void
    {
        $this->smsBuilder = resolve(SmsEventBuilder::class);
        $this->divisionBuilder = resolve(DivisionBuilder::class);
        $this->clientBuilder = resolve(ClientBuilder::class);
        $this->clientPhoneBuilder = resolve(PhoneBuilder::class);

        parent::setUp();
    }

    /** @test */
    public function create()
    {
        /** @var $division Division */
        $division = $this->divisionBuilder->create();

        /** @var $model SmsEvents */
        $model = $this->smsBuilder
            ->inbound(1)
            ->caller_id('+1999999999')
            ->caller_did('+1222222222')
            ->create();

        $this->assertEquals(0, CommunicationRecord::count());

        RecordCreateService::handler($model, ['division_id' => $division->id]);

        /** @var $rec CommunicationRecord */
        $rec = CommunicationRecord::first();

        $this->assertNull($rec->client_id);
        $this->assertEquals($rec->division_id, $division->id);
        $this->assertEquals($rec->type, Type::Inbound);
        $this->assertEquals($rec->channel_contact, '999999999');
        $this->assertEquals($rec->sort_at, $model->created_at);

        $this->assertFalse($rec->is_answered);

        $this->assertInstanceOf(SmsEvents::class, $rec->entity);
        $this->assertEquals($rec->entity->id, $model->id);
    }

    /** @test */
    public function create_check_outbound()
    {
        /** @var $division Division */
        $division = $this->divisionBuilder->create();

        /** @var $model SmsEvents */
        $model = $this->smsBuilder
            ->inbound(0)
            ->caller_id('+1999999999')
            ->caller_did('+1222222222')
            ->create();

        $this->assertEquals(0, CommunicationRecord::count());

        RecordCreateService::handler($model, ['division_id' => $division->id]);

        /** @var $rec CommunicationRecord */
        $rec = CommunicationRecord::first();

        $this->assertNull($rec->client_id);
        $this->assertEquals($rec->division_id, $division->id);
        $this->assertEquals($rec->type, Type::Outbound);
        $this->assertEquals($rec->channel_contact, '222222222');
        $this->assertEquals($rec->sort_at, $model->created_at);

        $this->assertTrue($rec->is_answered);

        $this->assertInstanceOf(SmsEvents::class, $rec->entity);
        $this->assertEquals($rec->entity->id, $model->id);
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

        /** @var $model SmsEvents */
        $model = $this->smsBuilder
            ->inbound(1)
            ->caller_id('+1'.$phoneValue)
            ->create();

        $this->assertEquals(0, CommunicationRecord::count());

        RecordCreateService::handler($model, ['division_id' => $division->id]);

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

        /** @var $model SmsEvents */
        $model = $this->smsBuilder
            ->inbound(1)
            ->caller_id('+1'.$phoneValue)
            ->create();

        $this->assertEquals(0, CommunicationRecord::count());

        RecordCreateService::handler($model, ['division_id' => $division->id]);

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
