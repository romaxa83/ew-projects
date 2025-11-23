<?php

namespace Tests\Unit\Services\Communications\FormatterService;

use App\Enums\Clients\ActivityType;
use App\Models\Client;
use App\Models\Client\Activity;
use App\Models\Communications\CommunicationRecord;
use App\Models\Division;
use App\Models\Order;
use App\Services\Communications\FormatterService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Clients\ActivityBuilder;
use Tests\Builders\Clients\ClientBuilder;
use Tests\Builders\Communications\CommunicationRecordBuilder;
use Tests\Builders\Communications\ConversationFavoritesBuilder;
use Tests\Builders\Divisions\DivisionBuilder;
use Tests\Builders\Orders\OrderBuilder;
use Tests\TestCase;

class OrderRecTest extends TestCase
{
    use DatabaseTransactions;

    protected ClientBuilder $clientBuilder;
    protected DivisionBuilder $divisionBuilder;
    protected OrderBuilder $orderBuilder;
    protected CommunicationRecordBuilder $communicationRecordBuilder;
    protected FormatterService $service;

    public function setUp(): void
    {
        $this->clientBuilder = resolve(ClientBuilder::class);
        $this->orderBuilder = resolve(OrderBuilder::class);
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

        /** @var $entity Order */
        $entity = $this->orderBuilder
            ->client($client)
            ->created_at($date)
            ->create();

        /** @var $model CommunicationRecord */
        $model = $this->communicationRecordBuilder
            ->entity($entity)
            ->client($client)
            ->division($division)
            ->create();

        $rec = $this->service->recForMainPanelBase($model);

        $this->assertEquals($rec['type'], FormatterService::getType($entity));
        $this->assertTrue($rec['item'] instanceof Order);
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

        /** @var $entity Order */
        $entity = $this->orderBuilder
            ->client($client)
            ->created_at($date)
            ->create();

        /** @var $model CommunicationRecord */
        $model = $this->communicationRecordBuilder
            ->entity($entity)
            ->client($client)
            ->division($division)
            ->create();

        $rec = $this->service->recForMainPanel($model);

        $this->assertEquals($rec['type'], FormatterService::getType($entity));
        $this->assertTrue($rec['item'] instanceof Order);
        $this->assertEquals($rec['item']->id, $entity->id);
        $this->assertEquals($rec['uid'], FormatterService::getUid($entity));
        $this->assertEquals($rec['id'], $model->id);
        $this->assertEquals($rec['client']->id, $client->id);
        $this->assertNull($rec['collectionClients']);
        $this->assertEquals($rec['timestamp'], $date->timestamp);
        $this->assertNull($rec['channelContact']);
        $this->assertFalse($rec['starred']);
        $this->assertTrue($rec['isAnswered']);
        $this->assertEquals($rec['managers'][0], $entity->user_id);
    }
}

