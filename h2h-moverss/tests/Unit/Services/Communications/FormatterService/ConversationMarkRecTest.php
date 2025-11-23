<?php

namespace Tests\Unit\Services\Communications\FormatterService;

use App\Models\Client;
use App\Models\Communications\CommunicationRecord;
use App\Models\Communications\ConversationMark;
use App\Services\Communications\FormatterService;
use App\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Clients\ClientBuilder;
use Tests\Builders\Communications\CommunicationRecordBuilder;
use Tests\Builders\Communications\ConversationFavoritesBuilder;
use Tests\Builders\Communications\ConversationMarkBuilder;
use Tests\Builders\Divisions\DivisionBuilder;
use Tests\Builders\Users\UserBuilder;
use Tests\TestCase;

class ConversationMarkRecTest extends TestCase
{
    use DatabaseTransactions;

    protected UserBuilder $userBuilder;
    protected ClientBuilder $clientBuilder;
    protected ConversationMarkBuilder $conversationMarkBuilder;
    protected DivisionBuilder $divisionBuilder;
    protected CommunicationRecordBuilder $communicationRecordBuilder;

    protected FormatterService $service;

    public function setUp(): void
    {
        $this->userBuilder = resolve(UserBuilder::class);
        $this->clientBuilder = resolve(ClientBuilder::class);
        $this->conversationMarkBuilder = resolve(ConversationMarkBuilder::class);
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

        /** @var $user User */
        $user = $this->userBuilder->create();
        /** @var $client Client */
        $client = $this->clientBuilder->create();

        /** @var $entity ConversationMark */
        $entity = $this->conversationMarkBuilder
            ->client($client)
            ->user($user)
            ->created_at($date)
            ->create();

        /** @var $model CommunicationRecord */
        $model = $this->communicationRecordBuilder
            ->entity($entity)
            ->client($client)
            ->create();

        $rec = $this->service->recForMainPanelBase($model);

        $this->assertEquals($rec['type'], FormatterService::getType($entity));
        $this->assertTrue($rec['item'] instanceof ConversationMark);
        $this->assertEquals($rec['item']->id, $entity->id);
        $this->assertEquals($rec['item']->user->id, $user->id);
        $this->assertEquals($rec['uid'], FormatterService::getUid($entity));
        $this->assertEquals($rec['id'], $model->id);
        $this->assertEquals($rec['timestamp'], $date->timestamp);
    }
}

