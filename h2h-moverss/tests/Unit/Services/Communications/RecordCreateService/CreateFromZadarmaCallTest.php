<?php

namespace Tests\Unit\Services\Communications\RecordCreateService;

use App\Enums\Communications\Type;
use App\Models\Communications\CommunicationRecord;
use App\Models\Division;
use App\Models\Zadarma\CallsEvents;
use App\Services\Communications\RecordCreateService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Clients\ClientBuilder;
use Tests\Builders\Clients\PhoneBuilder;
use Tests\Builders\Divisions\DivisionBuilder;
use Tests\Builders\Zadarma\CallEventBuilder;
use Tests\TestCase;

class CreateFromZadarmaCallTest extends TestCase
{
    use DatabaseTransactions;

    protected CallEventBuilder $callBuilder;
    protected DivisionBuilder $divisionBuilder;
    protected ClientBuilder $clientBuilder;
    protected PhoneBuilder $clientPhoneBuilder;

    public function setUp(): void
    {
        $this->callBuilder = resolve(CallEventBuilder::class);
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

        /** @var $model CallsEvents */
        $model = $this->callBuilder
            ->pbx_call_id('in_1111')
            ->caller_id('+1999999999')
            ->disposition(CallsEvents::DISPOSITION_ANSWERED)
            ->event(CallsEvents::EVENT_NOTIFY_END)
            ->create();

        $this->assertEquals(0, CommunicationRecord::count());

        RecordCreateService::handler($model, ['division_id' => $division->id]);

        /** @var $rec CommunicationRecord */
        $rec = CommunicationRecord::first();

        $this->assertNull($rec->client_id);
        $this->assertEquals($rec->division_id, $division->id);
        $this->assertEquals($rec->type, Type::Inbound);
        $this->assertEquals($rec->channel_contact, '999999999');
//        $this->assertEquals($rec->sort_at, $model->created_at);

        $this->assertTrue($rec->is_answered);

        $this->assertInstanceOf(CallsEvents::class, $rec->entity);
        $this->assertEquals($rec->entity->id, $model->id);
    }

    /** @test */
    public function create_check_anonymous()
    {
        /** @var $division Division */
        $division = $this->divisionBuilder->create();

        /** @var $model CallsEvents */
        $model = $this->callBuilder
            ->pbx_call_id('in_1111')
            ->caller_id(null)
            ->disposition(CallsEvents::DISPOSITION_CANCEL)
            ->event(CallsEvents::EVENT_NOTIFY_END)
            ->create();

        $this->assertEquals(0, CommunicationRecord::count());

        RecordCreateService::handler($model, ['division_id' => $division->id]);

        /** @var $rec CommunicationRecord */
        $rec = CommunicationRecord::first();

        $this->assertEquals($rec->type, Type::Inbound);
        $this->assertEquals($rec->channel_contact, 'Anonymous-'.$model->id);
        $this->assertFalse($rec->is_answered);
    }

    /** @test */
    public function create_check_outbound()
    {
        /** @var $division Division */
        $division = $this->divisionBuilder->create();

        /** @var $model CallsEvents */
        $model = $this->callBuilder
            ->pbx_call_id('out_1111')
            ->destination('+1333333333')
            ->event(CallsEvents::EVENT_NOTIFY_OUT_END)
            ->create();

        $this->assertEquals(0, CommunicationRecord::count());

        RecordCreateService::handler($model, ['division_id' => $division->id]);

        /** @var $rec CommunicationRecord */
        $rec = CommunicationRecord::first();

        $this->assertEquals($rec->type, Type::Outbound);
        $this->assertEquals($rec->channel_contact, '333333333');
        $this->assertTrue($rec->is_answered);
    }

    /** @test */
    public function create_check_outbound_cleared_phone_1()
    {
        /** @var $division Division */
        $division = $this->divisionBuilder->create();

        /** @var $model CallsEvents */
        $model = $this->callBuilder
            ->pbx_call_id('out_1111')
            ->destination('+1000277777')
            ->create();

        $this->assertEquals(0, CommunicationRecord::count());

        RecordCreateService::handler($model, ['division_id' => $division->id]);

        /** @var $rec CommunicationRecord */
        $rec = CommunicationRecord::first();

        $this->assertEquals($rec->channel_contact, '77777');
    }

    /** @test */
    public function create_check_outbound_cleared_phone_2()
    {
        /** @var $division Division */
        $division = $this->divisionBuilder->create();

        /** @var $model CallsEvents */
        $model = $this->callBuilder
            ->pbx_call_id('out_1111')
            ->destination('+1000177777')
            ->create();

        $this->assertEquals(0, CommunicationRecord::count());

        RecordCreateService::handler($model, ['division_id' => $division->id]);

        /** @var $rec CommunicationRecord */
        $rec = CommunicationRecord::first();

        $this->assertEquals($rec->channel_contact, '77777');
    }

    /** @test */
    public function create_check_outbound_cleared_phone_3()
    {
        /** @var $division Division */
        $division = $this->divisionBuilder->create();

        /** @var $model CallsEvents */
        $model = $this->callBuilder
            ->pbx_call_id('out_1111')
            ->destination('+188877777')
            ->create();

        $this->assertEquals(0, CommunicationRecord::count());

        RecordCreateService::handler($model, ['division_id' => $division->id]);

        /** @var $rec CommunicationRecord */
        $rec = CommunicationRecord::first();

        $this->assertEquals($rec->channel_contact, '77777');
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

        /** @var $model CallsEvents */
        $model = $this->callBuilder
            ->pbx_call_id('in_1111')
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

        /** @var $model CallsEvents */
        $model = $this->callBuilder
            ->pbx_call_id('in_1111')
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
