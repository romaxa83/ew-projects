<?php

namespace Tests\Unit\Services\Communications\RecordCreateService;

use App\Enums\Clients\ActivityType;
use App\Enums\Communications\Type;
use App\Models\Client;
use App\Models\Client\Activity;
use App\Models\Communications\CommunicationRecord;
use App\Models\Division;
use App\Services\Communications\RecordCreateService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Clients\ActivityBuilder;
use Tests\Builders\Clients\ClientBuilder;
use Tests\Builders\Divisions\DivisionBuilder;
use Tests\TestCase;

class CreateFromClientActivityTest extends TestCase
{
    use DatabaseTransactions;

    protected ClientBuilder $clientBuilder;
    protected ActivityBuilder $activityBuilder;
    protected DivisionBuilder $divisionBuilder;

    public function setUp(): void
    {
        $this->clientBuilder = resolve(ClientBuilder::class);
        $this->activityBuilder = resolve(ActivityBuilder::class);
        $this->divisionBuilder = resolve(DivisionBuilder::class);

        parent::setUp();
    }

    /** @test */
    public function create()
    {
        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        /** @var $client Client */
        $client = $this->clientBuilder->create();

        /** @var $model Activity */
        $model = $this->activityBuilder
            ->client($client)
            ->type(ActivityType::Customer_inventory_save())
            ->miscs(['division_id' => $division->id])
            ->create();

        $this->assertEquals(0, CommunicationRecord::count());

        RecordCreateService::handler($model);

        /** @var $rec CommunicationRecord */
        $rec = CommunicationRecord::first();

        $this->assertEquals($rec->client_id, $client->id);
        $this->assertEquals($rec->division_id, $division->id);
        $this->assertEquals($rec->type, Type::Inner);
        $this->assertEquals($rec->channel_contact, $client->id);
        $this->assertEquals($rec->sort_at, $model->created_at);

        $this->assertFalse($rec->is_answered);

        $this->assertInstanceOf(Activity::class, $rec->entity);
        $this->assertEquals($rec->entity->id, $model->id);
    }

    /** @test */
    public function not_create_wrong_activity_type()
    {
        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        /** @var $client Client */
        $client = $this->clientBuilder->create();
        /** @var $model Activity */
        $model = $this->activityBuilder
            ->client($client)
            ->miscs(['division_id' => $division->id])
            ->create();

        $this->assertEquals(0, CommunicationRecord::count());

        RecordCreateService::handler($model);

        $this->assertEquals(0, CommunicationRecord::count());
    }

    /** @test */
    public function not_create_not_have_division()
    {
        /** @var $client Client */
        $client = $this->clientBuilder->create();
        /** @var $model Activity */
        $model = $this->activityBuilder
            ->client($client)
            ->type(ActivityType::Customer_inventory_save())
            ->create();

        $this->assertEquals(0, CommunicationRecord::count());

        RecordCreateService::handler($model);

        $this->assertEquals(0, CommunicationRecord::count());
    }
}
