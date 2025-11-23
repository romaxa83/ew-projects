<?php

namespace Tests\Unit\Services\Communications\RecordCreateService;

use App\Enums\Communications\Type;
use App\Models\Communications\CommunicationRecord;
use App\Models\Division;
use App\Models\Ringostat\EventAfterCall;
use App\Services\Communications\RecordCreateService;
use Tests\Builders\Clients\PhoneBuilder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Clients\ClientBuilder;
use Tests\Builders\Divisions\DivisionBuilder;
use Tests\Builders\Ringostat;
use Tests\TestCase;

class CreateFromRingostatTest extends TestCase
{
    use DatabaseTransactions;

    protected Ringostat\EventAfterCallBuilder $callBuilder;
    protected DivisionBuilder $divisionBuilder;
    protected ClientBuilder $clientBuilder;
    protected PhoneBuilder $clientPhoneBuilder;

    public function setUp(): void
    {
        $this->callBuilder = resolve(Ringostat\EventAfterCallBuilder::class);
        $this->divisionBuilder = resolve(DivisionBuilder::class);
        $this->clientBuilder = resolve(ClientBuilder::class);
        $this->clientPhoneBuilder = resolve(PhoneBuilder::class);

        parent::setUp();
    }

    /** @test */
    public function create()
    {
        $ringostatProjectId = '1111';
        /** @var $division Division */
        $division = $this->divisionBuilder
            ->misc(['ringostat_project_id' => $ringostatProjectId])
            ->create();

        /** @var $model EventAfterCall */
        $model = $this->callBuilder
            ->projectId($ringostatProjectId)
            ->callerNumber('+1999999999')
            ->destination('+1888888888')
            ->type('in')
            ->status(EventAfterCall::STATUS_NO_ANSWER)
            ->create();

        $this->assertEquals(0, CommunicationRecord::count());

        RecordCreateService::handler($model);

        /** @var $rec CommunicationRecord */
        $rec = CommunicationRecord::first();

        $this->assertNull($rec->client_id);
        $this->assertNull($rec->client_ids);
        $this->assertEquals($rec->division_id, $division->id);
        $this->assertEquals($rec->type, Type::Inbound);
        $this->assertEquals($rec->channel_contact, '999999999');

        $this->assertFalse($rec->is_answered);

        $this->assertInstanceOf(EventAfterCall::class, $rec->entity);
        $this->assertEquals($rec->entity->id, $model->id);
    }

    /** @test */
    public function create_check_out()
    {
        $ringostatProjectId = '1111';
        /** @var $division Division */
        $division = $this->divisionBuilder
            ->misc(['ringostat_project_id' => $ringostatProjectId])
            ->create();

        /** @var $model EventAfterCall */
        $model = $this->callBuilder
            ->projectId($ringostatProjectId)
            ->callerNumber('+1999999999')
            ->destination('+1888888888')
            ->type('out')
            ->create();

        $this->assertEquals(0, CommunicationRecord::count());

        RecordCreateService::handler($model);

        /** @var $rec CommunicationRecord */
        $rec = CommunicationRecord::first();

        $this->assertEquals($rec->channel_contact, '888888888');
        $this->assertEquals($rec->type, Type::Outbound);

        $this->assertTrue($rec->is_answered);

        $this->assertInstanceOf(EventAfterCall::class, $rec->entity);
        $this->assertEquals($rec->entity->id, $model->id);
    }

    /** @test */
    public function create_check_in_and_status_answered()
    {
        $ringostatProjectId = '1111';
        /** @var $division Division */
        $division = $this->divisionBuilder
            ->misc(['ringostat_project_id' => $ringostatProjectId])
            ->create();

        /** @var $model EventAfterCall */
        $model = $this->callBuilder
            ->projectId($ringostatProjectId)
            ->callerNumber('+1999999999')
            ->destination('+1888888888')
            ->type('in')
            ->status(EventAfterCall::STATUS_ANSWERED)
            ->create();

        $this->assertEquals(0, CommunicationRecord::count());

        RecordCreateService::handler($model);

        /** @var $rec CommunicationRecord */
        $rec = CommunicationRecord::first();

        $this->assertTrue($rec->is_answered);

        $this->assertInstanceOf(EventAfterCall::class, $rec->entity);
        $this->assertEquals($rec->entity->id, $model->id);
    }

    /** @test */
    public function check_detect_client()
    {
        $ringostatProjectId = '1111';
        $this->divisionBuilder
            ->misc(['ringostat_project_id' => $ringostatProjectId])
            ->create();

        $phoneValue = '999999999';
        $client = $this->clientBuilder->create();
        $this->clientPhoneBuilder
            ->client($client)
            ->value($phoneValue)
            ->create();

        /** @var $model EventAfterCall */
        $model = $this->callBuilder
            ->projectId($ringostatProjectId)
            ->callerNumber('+1' . $phoneValue)
            ->type('in')
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
        $ringostatProjectId = '1111';
        $this->divisionBuilder
            ->misc(['ringostat_project_id' => $ringostatProjectId])
            ->create();

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

        /** @var $model EventAfterCall */
        $model = $this->callBuilder
            ->projectId($ringostatProjectId)
            ->callerNumber('+1' . $phoneValue)
            ->type('in')
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

