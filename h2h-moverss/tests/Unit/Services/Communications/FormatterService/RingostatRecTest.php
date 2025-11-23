<?php

namespace Tests\Unit\Services\Communications\FormatterService;

use App\Models\Client;
use App\Models\Communications\CommunicationRecord;
use App\Models\Division;
use App\Models\Ringostat\EventAfterCall;
use App\Services\Communications\FormatterService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Clients\ClientBuilder;
use Tests\Builders\Communications\CommunicationRecordBuilder;
use Tests\Builders\Communications\ConversationFavoritesBuilder;
use Tests\Builders\Divisions\DivisionBuilder;
use Tests\Builders\Ringostat\EventAfterCallBuilder;
use Tests\TestCase;

class RingostatRecTest extends TestCase
{
    use DatabaseTransactions;

    protected ClientBuilder $clientBuilder;
    protected EventAfterCallBuilder $eventAfterCallBuilder;
    protected DivisionBuilder $divisionBuilder;
    protected CommunicationRecordBuilder $communicationRecordBuilder;
    protected ConversationFavoritesBuilder $conversationFavoritesBuilder;
    protected FormatterService $service;

    public function setUp(): void
    {
        $this->clientBuilder = resolve(ClientBuilder::class);
        $this->eventAfterCallBuilder = resolve(EventAfterCallBuilder::class);
        $this->divisionBuilder = resolve(DivisionBuilder::class);
        $this->communicationRecordBuilder = resolve(CommunicationRecordBuilder::class);
        $this->conversationFavoritesBuilder = resolve(ConversationFavoritesBuilder::class);
        $this->service = resolve(FormatterService::class);


        parent::setUp();
    }

    /** @test */
    public function one_simple()
    {
        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        /** @var $client Client */
        $client = $this->clientBuilder->create();

        /** @var $entity EventAfterCall */
        $entity = $this->eventAfterCallBuilder
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
        $this->assertTrue($rec['item'] instanceof EventAfterCall);
        $this->assertEquals($rec['item']->id, $entity->id);
        $this->assertEquals($rec['uid'], FormatterService::getUid($entity));
        $this->assertEquals($rec['id'], $model->id);
    }

    /** @test */
    public function one_advance()
    {
        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        /** @var $client Client */
        $client = $this->clientBuilder->create();

        /** @var $entity EventAfterCall */
        $entity = $this->eventAfterCallBuilder
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
        $this->assertTrue($rec['item'] instanceof EventAfterCall);
        $this->assertEquals($rec['item']->id, $entity->id);
        $this->assertEquals($rec['uid'], FormatterService::getUid($entity));
        $this->assertEquals($rec['id'], $model->id);
        $this->assertEquals($rec['client']->id, $client->id);
        $this->assertNull($rec['collectionClients']);
//        $this->assertEquals($rec['timestamp'], $date->timestamp);
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

        /** @var $entity EventAfterCall */
        $entity = $this->eventAfterCallBuilder
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
        $user = $this->loginUser();

        /** @var $client Client */
        $client = $this->clientBuilder->create();

        $this->conversationFavoritesBuilder
            ->client($client)
            ->user($user)
            ->starred(true)
            ->create();

        /** @var $entity EventAfterCall */
        $entity = $this->eventAfterCallBuilder
            ->create();

        /** @var $model CommunicationRecord */
        $model = $this->communicationRecordBuilder
            ->entity($entity)
            ->client($client)
            ->create();

        $rec = $this->service->recForMainPanel($model);

        $this->assertTrue($rec['starred']);
    }
}
