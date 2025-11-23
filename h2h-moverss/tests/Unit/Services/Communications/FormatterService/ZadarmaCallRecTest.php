<?php

namespace Tests\Unit\Services\Communications\FormatterService;

use App\Enums\Communications\ConversationContactType;
use App\Models\Client;
use App\Models\Communications\CommunicationRecord;
use App\Models\Division;
use App\Models\Zadarma\CallsEvents;
use App\Services\Communications\FormatterService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Clients\ClientBuilder;
use Tests\Builders\Communications\CommunicationRecordBuilder;
use Tests\Builders\Communications\ConversationFavoritesBuilder;
use Tests\Builders\Divisions\DivisionBuilder;
use Tests\Builders\Zadarma\CallEventBuilder;
use Tests\TestCase;

class ZadarmaCallRecTest extends TestCase
{
    use DatabaseTransactions;

    protected ClientBuilder $clientBuilder;
    protected CallEventBuilder $callBuilder;
    protected DivisionBuilder $divisionBuilder;
    protected CommunicationRecordBuilder $communicationRecordBuilder;
    protected ConversationFavoritesBuilder $conversationFavoritesBuilder;
    protected FormatterService $service;

    public function setUp(): void
    {
        $this->clientBuilder = resolve(ClientBuilder::class);
        $this->callBuilder = resolve(CallEventBuilder::class);
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

        /** @var $entity CallsEvents */
        $entity = $this->callBuilder
            ->call_start_at($date)
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
        $this->assertTrue($rec['item'] instanceof CallsEvents);
        $this->assertEquals($rec['item']->id, $entity->id);
        $this->assertEquals($rec['uid'], FormatterService::getUid($entity));
        $this->assertEquals($rec['id'], $model->id);
        $this->assertEquals($rec['timestamp'], $date->timestamp);

        $this->assertEquals($rec['item']->disposition, $entity->disposition);
    }

    /** @test */
    public function one_advance()
    {
        $date = CarbonImmutable::now();

        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        /** @var $client Client */
        $client = $this->clientBuilder->create();

        /** @var $entity CallsEvents */
        $entity = $this->callBuilder
            ->call_start_at($date)
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
        $this->assertTrue($rec['item'] instanceof CallsEvents);
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

        $this->assertEquals($rec['item']->disposition, $entity->disposition);
    }

    /** @test */
    public function one_advance_disposition_as_voicemail()
    {
        $date = CarbonImmutable::now();

        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        /** @var $client Client */
        $client = $this->clientBuilder->create();

        /** @var $entity CallsEvents */
        $entity = $this->callBuilder
            ->call_start_at($date)
            ->event(CallsEvents::EVENT_NOTIFY_END)
            ->status_code(16)
            ->internal(null)
            ->disposition(CallsEvents::DISPOSITION_ANSWERED)
            ->create();

        /** @var $model CommunicationRecord */
        $model = $this->communicationRecordBuilder
            ->entity($entity)
            ->client($client)
            ->division($division)
            ->channel_contact($client->id)
            ->create();

        $rec = $this->service->recForMainPanel($model);

        $this->assertNotEquals($rec['item']->disposition, $entity->disposition);
        $this->assertEquals($rec['item']->disposition, CallsEvents::DISPOSITION_VOICEMAIL);
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

        /** @var $entity CallsEvents */
        $entity = $this->callBuilder
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

        /** @var $entity CallsEvents */
        $entity = $this->callBuilder
            ->create();

        /** @var $model CommunicationRecord */
        $model = $this->communicationRecordBuilder
            ->entity($entity)
            ->client($client)
            ->create();

        $rec = $this->service->recForMainPanel($model);

        $this->assertFalse($rec['starred']);
    }

    /** @test */
    public function one_advance_is_starred_but_not_have_client()
    {
        $user = $this->loginUser();

        /** @var $entity CallsEvents */
        $entity = $this->callBuilder
            ->create();

        /** @var $model CommunicationRecord */
        $model = $this->communicationRecordBuilder
            ->entity($entity)
            ->channel_contact($entity->destination)
            ->create();

        $this->conversationFavoritesBuilder
            ->user($user)
            ->contact_type(ConversationContactType::Phone->value)
            ->contact_value($entity->destination)
            ->starred(true)
            ->create();

        $rec = $this->service->recForMainPanel($model);

        $this->assertTrue($rec['starred']);
    }

    /** @test */
    public function one_advance_is_starred_but_not_have_client_and_another_user()
    {
        /** @var $entity CallsEvents */
        $entity = $this->callBuilder
            ->create();

        /** @var $model CommunicationRecord */
        $model = $this->communicationRecordBuilder
            ->entity($entity)
            ->channel_contact($entity->destination)
            ->create();

        $this->conversationFavoritesBuilder
            ->contact_type(ConversationContactType::Phone->value)
            ->contact_value($entity->destination)
            ->starred(true)
            ->create();

        $rec = $this->service->recForMainPanel($model);

        $this->assertFalse($rec['starred']);
    }
}
