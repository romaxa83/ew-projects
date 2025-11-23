<?php

namespace Tests\Unit\Services\Communications\RecordCreateService;

use App\Enums\Communications\Type;
use App\Models\Client;
use App\Models\Communications\CommunicationRecord;
use App\Models\Division;
use App\Models\Mailbox\Gmail\Account;
use App\Models\Mailbox\Gmail\Message;
use App\Models\Ringostat\EventAfterCall;
use App\Services\Communications\RecordCreateService;
use Carbon\CarbonImmutable;
use Tests\Builders\Clients\EmailBuilder;
use Tests\Builders\Clients\PhoneBuilder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Clients\ClientBuilder;
use Tests\Builders\Divisions\DivisionBuilder;
use Tests\Builders\Gmail\AccountBuilder;
use Tests\Builders\Gmail\MessageBuilder;
use Tests\Builders\Ringostat;
use Tests\TestCase;

class CreateFromGmailMessageTest extends TestCase
{
    use DatabaseTransactions;

    protected Ringostat\EventAfterCallBuilder $callBuilder;
    protected DivisionBuilder $divisionBuilder;
    protected ClientBuilder $clientBuilder;
    protected EmailBuilder $clientEmailBuilder;
    protected AccountBuilder $gmailAccountBuilder;
    protected MessageBuilder $gmailMessageBuilder;

    public function setUp(): void
    {
        $this->callBuilder = resolve(Ringostat\EventAfterCallBuilder::class);
        $this->divisionBuilder = resolve(DivisionBuilder::class);
        $this->clientBuilder = resolve(ClientBuilder::class);
        $this->clientEmailBuilder = resolve(EmailBuilder::class);
        $this->gmailAccountBuilder = resolve(AccountBuilder::class);
        $this->gmailMessageBuilder = resolve(MessageBuilder::class);

        parent::setUp();
    }

    /** @test */
    public function create()
    {
        $now = CarbonImmutable::now()->addDay();
        $email = 'test@gmail.com';
        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        /** @var $account Account */
        $account = $this->gmailAccountBuilder->division($division)->create();

        /** @var $model Message */
        $model = $this->gmailMessageBuilder
            ->account($account)
            ->tag(Message::TAG_SENT)
            ->misc(["from" => ["email" => $email]])
            ->updated_at($now)
            ->create();

//        $this->assertEquals(0, CommunicationRecord::count());

        RecordCreateService::handler($model);

        /** @var $rec CommunicationRecord */
        $rec = CommunicationRecord::first();

        $this->assertNull($rec->client_id);
        $this->assertNull($rec->client_ids);
        $this->assertEquals($rec->division_id, $division->id);
        $this->assertEquals($rec->type, Type::Outbound);
        $this->assertEquals($rec->channel_contact, $email);
        $this->assertEquals($rec->sort_at->timestamp, $now->timestamp);

        $this->assertTrue($rec->is_answered);

        $this->assertInstanceOf(Message::class, $rec->entity);
        $this->assertEquals($rec->entity->id, $model->id);
    }

    /** @test */
    public function check_inbound()
    {
        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        /** @var $account Account */
        $account = $this->gmailAccountBuilder->division($division)->create();

        /** @var $model Message */
        $model = $this->gmailMessageBuilder
            ->account($account)
            ->tag(Message::TAG_INBOX)
            ->create();

//        $this->assertEquals(0, CommunicationRecord::count());

        RecordCreateService::handler($model);

        /** @var $rec CommunicationRecord */
        $rec = CommunicationRecord::first();

        $this->assertEquals($rec->type, Type::Inbound);
        $this->assertFalse($rec->is_answered);
    }

    /** @test */
    public function check_detect_client()
    {
        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        /** @var $account Account */
        $account = $this->gmailAccountBuilder->division($division)->create();

        $email = 'test@gmail.com';
        /** @var $client Client */
        $client = $this->clientBuilder->create();
        $this->clientEmailBuilder
            ->client($client)
            ->value($email)
            ->create();

        /** @var $model Message */
        $model = $this->gmailMessageBuilder
            ->account($account)
            ->misc(["from" => ["email" => $email]])
            ->create();

//        $this->assertEquals(0, CommunicationRecord::count());

        RecordCreateService::handler($model);

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
        /** @var $account Account */
        $account = $this->gmailAccountBuilder->division($division)->create();

        $email = 'test@gmail.com';
        /** @var $client Client */
        $client_1 = $this->clientBuilder->create();
        $client_2 = $this->clientBuilder->create();
        $client_3 = $this->clientBuilder->create();

        $this->clientEmailBuilder
            ->client($client_1)
            ->value($email)
            ->create();
        $this->clientEmailBuilder
            ->client($client_2)
            ->value($email)
            ->create();
        $this->clientEmailBuilder
            ->client($client_3)
            ->value($email)
            ->create();

        /** @var $model Message */
        $model = $this->gmailMessageBuilder
            ->account($account)
            ->misc(["from" => ["email" => $email]])
            ->create();

//        $this->assertEquals(0, CommunicationRecord::count());

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

    /** @test */
    public function fail_no_type()
    {
        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        /** @var $account Account */
        $account = $this->gmailAccountBuilder->division($division)->create();

        /** @var $model Message */
        $model = $this->gmailMessageBuilder
            ->account($account)
            ->tag(Message::TAG_TRASH)
            ->create();

//        $this->assertEquals(0, CommunicationRecord::count());

        RecordCreateService::handler($model);

        /** @var $rec CommunicationRecord */
        $rec = CommunicationRecord::first();

        $this->assertNull($rec);
    }
}

