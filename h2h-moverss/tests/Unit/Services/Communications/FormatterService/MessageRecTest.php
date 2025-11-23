<?php

namespace Tests\Unit\Services\Communications\FormatterService;

use App\Models\Client;
use App\Models\Communications\CommunicationRecord;
use App\Models\Division;
use App\Models\Mailbox\Gmail\Message;
use App\Services\Communications\FormatterService;
use App\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Clients\ClientBuilder;
use Tests\Builders\Communications\CommunicationRecordBuilder;
use Tests\Builders\Communications\ConversationFavoritesBuilder;
use Tests\Builders\Divisions\DivisionBuilder;
use Tests\Builders\Gmail\MessageBuilder;
use Tests\TestCase;

class MessageRecTest extends TestCase
{
    use DatabaseTransactions;

    protected ClientBuilder $clientBuilder;
    protected MessageBuilder $messageBuilder;
    protected DivisionBuilder $divisionBuilder;
    protected CommunicationRecordBuilder $communicationRecordBuilder;
    protected ConversationFavoritesBuilder $conversationFavoritesBuilder;
    protected FormatterService $service;

    public function setUp(): void
    {
        $this->clientBuilder = resolve(ClientBuilder::class);
        $this->messageBuilder = resolve(MessageBuilder::class);
        $this->divisionBuilder = resolve(DivisionBuilder::class);
        $this->communicationRecordBuilder = resolve(CommunicationRecordBuilder::class);
        $this->conversationFavoritesBuilder = resolve(ConversationFavoritesBuilder::class);
        $this->service = resolve(FormatterService::class);


        parent::setUp();
    }

    /** @test */
    public function one_simple()
    {
        $user = $this->loginUser();

        $date = CarbonImmutable::now();

        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        /** @var $client Client */
        $client = $this->clientBuilder->create();

        /** @var $entity Message */
        $entity = $this->messageBuilder
            ->tags(Message::TAG_SENT)
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
        $this->assertTrue($rec['item'] instanceof Message);
        $this->assertEquals($rec['item']->id, $entity->id);
        $this->assertEquals($rec['uid'], FormatterService::getUid($entity));
        $this->assertEquals($rec['id'], $model->id);
        $this->assertEquals($rec['timestamp'], $date->timestamp);

        $this->assertEquals($rec['audit'], [
            'audit_event' => 'created',
            'user_type' => User::class,
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_email' => $user->email,
        ]);
    }

    /** @test */
    public function one_simple_without_audit()
    {
        $date = CarbonImmutable::now();

        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        /** @var $client Client */
        $client = $this->clientBuilder->create();

        /** @var $entity Message */
        $entity = $this->messageBuilder
            ->tags(Message::TAG_TRASH)
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
        $this->assertTrue($rec['item'] instanceof Message);
        $this->assertEquals($rec['item']->id, $entity->id);
        $this->assertEquals($rec['uid'], FormatterService::getUid($entity));
        $this->assertEquals($rec['id'], $model->id);
        $this->assertEquals($rec['timestamp'], $date->timestamp);

        $this->assertNull($rec['audit']);
    }

    /** @test */
    public function one_advance()
    {
        $date = CarbonImmutable::now();

        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        /** @var $client Client */
        $client = $this->clientBuilder->create();

        /** @var $entity Message */
        $entity = $this->messageBuilder
            ->tags(Message::TAG_TRASH)
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
        $this->assertTrue($rec['item'] instanceof Message);
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

        $this->assertNull($rec['audit']);
    }
}
