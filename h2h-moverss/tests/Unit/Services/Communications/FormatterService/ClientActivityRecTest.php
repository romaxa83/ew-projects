<?php

namespace Tests\Unit\Services\Communications\FormatterService;

use App\Enums\Clients\ActivityType;
use App\Models\Client;
use App\Models\Client\Activity;
use App\Models\Communications\CommunicationRecord;
use App\Models\Division;
use App\Services\Communications\FormatterService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Clients\ActivityBuilder;
use Tests\Builders\Clients\ClientBuilder;
use Tests\Builders\Communications\CommunicationRecordBuilder;
use Tests\Builders\Communications\ConversationFavoritesBuilder;
use Tests\Builders\Divisions\DivisionBuilder;
use Tests\Builders\Users\UserBuilder;
use Tests\TestCase;

class ClientActivityRecTest extends TestCase
{
    use DatabaseTransactions;

    protected ClientBuilder $clientBuilder;
    protected ActivityBuilder $activityBuilder;
    protected DivisionBuilder $divisionBuilder;
    protected CommunicationRecordBuilder $communicationRecordBuilder;
    protected ConversationFavoritesBuilder $conversationFavoritesBuilder;
    protected FormatterService $service;

    public function setUp(): void
    {
        $this->clientBuilder = resolve(ClientBuilder::class);
        $this->activityBuilder = resolve(ActivityBuilder::class);
        $this->divisionBuilder = resolve(DivisionBuilder::class);
        $this->communicationRecordBuilder = resolve(CommunicationRecordBuilder::class);
        $this->conversationFavoritesBuilder = resolve(ConversationFavoritesBuilder::class);
        $this->service = resolve(FormatterService::class);


        parent::setUp();
    }

    /** @test */
    public function one_simple()
    {
        $date = CarbonImmutable::now();

        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        /** @var $client Client */
        $client = $this->clientBuilder->create();

        /** @var $entity Activity */
        $entity = $this->activityBuilder
            ->client($client)
            ->type(ActivityType::Customer_inventory_save->value)
            ->miscs(['division_id' => $division->id])
            ->created_at($date)
            ->create();

        /** @var $model CommunicationRecord */
        $model = $this->communicationRecordBuilder
            ->entity($entity)
            ->client($client)
            ->division($division)
            ->channel_contact($client->id)
            ->create();

        $rec = $this->service->recForMainPanelBase($model);

        $this->assertEquals($rec['type'], FormatterService::getType($entity));
        $this->assertTrue($rec['item'] instanceof Activity);
        $this->assertEquals($rec['item']->id, $entity->id);
        $this->assertEquals($rec['uid'], FormatterService::getUid($entity));
        $this->assertEquals($rec['id'], $model->id);
        $this->assertEquals($rec['timestamp'], $date->timestamp);
    }

    /** @test */
    public function one_advance()
    {
        $date = CarbonImmutable::now();

        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        /** @var $client Client */
        $client = $this->clientBuilder->create();

        /** @var $entity Activity */
        $entity = $this->activityBuilder
            ->client($client)
            ->type(ActivityType::Customer_inventory_save->value)
            ->miscs(['division_id' => $division->id])
            ->created_at($date)
            ->create();

        /** @var $model CommunicationRecord */
        $model = $this->communicationRecordBuilder
            ->entity($entity)
            ->client($client)
            ->division($division)
            ->channel_contact($client->id)
            ->create();

        $rec = $this->service->recForMainPanel($model);

        $this->assertEquals($rec['type'], FormatterService::getType($entity));
        $this->assertTrue($rec['item'] instanceof Activity);
        $this->assertEquals($rec['item']->id, $entity->id);
        $this->assertEquals($rec['uid'], FormatterService::getUid($entity));
        $this->assertEquals($rec['id'], $model->id);
        $this->assertEquals($rec['client']->id, $client->id);
        $this->assertNull($rec['collectionClients']);
        $this->assertEquals($rec['timestamp'], $date->timestamp);
        $this->assertEquals($rec['channelContact'], $client->id);
        $this->assertFalse($rec['starred']);
        $this->assertTrue($rec['isAnswered']);
        $this->assertEmpty($rec['managers']);
        $this->assertNull($rec['managerAbbr']);
    }

    /** @test */
    public function one_advance_is_starred()
    {
        $user = $this->loginUser();

        /** @var $client Client */
        $client = $this->clientBuilder->create();

        $this->conversationFavoritesBuilder
            ->client($client)
            ->user($user)
            ->starred(true)
            ->create();

        /** @var $entity Activity */
        $entity = $this->activityBuilder
            ->client($client)
            ->type(ActivityType::Customer_inventory_save->value)
            ->create();

        /** @var $model CommunicationRecord */
        $model = $this->communicationRecordBuilder
            ->entity($entity)
            ->client($client)
            ->create();

        $rec = $this->service->recForMainPanel($model);

        $this->assertTrue($rec['starred']);
    }

    /** @test */
    public function one_advance_is_starred_but_another_user()
    {
        /** @var $client Client */
        $client = $this->clientBuilder->create();

        $this->conversationFavoritesBuilder
            ->client($client)
            ->starred(true)
            ->create();

        /** @var $entity Activity */
        $entity = $this->activityBuilder
            ->client($client)
            ->type(ActivityType::Customer_inventory_save->value)
            ->create();

        /** @var $model CommunicationRecord */
        $model = $this->communicationRecordBuilder
            ->entity($entity)
            ->client($client)
            ->create();

        $rec = $this->service->recForMainPanel($model);

        $this->assertFalse($rec['starred']);
    }
}

