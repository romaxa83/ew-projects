<?php

namespace Tests\Unit\Services\Communications\RecordCreateService;

use App\Enums\Communications\ConversationContactType;
use App\Enums\Communications\Type;
use App\Models\Client;
use App\Models\Communications\CommunicationRecord;
use App\Models\Communications\ConversationMark;
use App\Models\Division;
use App\Services\Communications\RecordCreateService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Clients\ClientBuilder;
use Tests\Builders\Clients\EmailBuilder;
use Tests\Builders\Clients\PhoneBuilder;
use Tests\Builders\Communications\ConversationMarkBuilder;
use Tests\Builders\Divisions\DivisionBuilder;
use Tests\TestCase;

class CreateFromConversationMarkTest extends TestCase
{
    use DatabaseTransactions;

    protected ClientBuilder $clientBuilder;
    protected PhoneBuilder $phoneBuilder;
    protected EmailBuilder $emailBuilder;
    protected ConversationMarkBuilder $conversationMarkBuilder;
    protected DivisionBuilder $divisionBuilder;

    public function setUp(): void
    {
        $this->clientBuilder = resolve(ClientBuilder::class);
        $this->phoneBuilder = resolve(PhoneBuilder::class);
        $this->emailBuilder = resolve(EmailBuilder::class);
        $this->conversationMarkBuilder = resolve(ConversationMarkBuilder::class);
        $this->divisionBuilder = resolve(DivisionBuilder::class);

        parent::setUp();
    }

    /** @test */
    public function create_with_client()
    {
        /** @var $client Client */
        $client = $this->clientBuilder->create();

        /** @var $model ConversationMark */
        $model = $this->conversationMarkBuilder
            ->client($client)
            ->create();

        $this->assertEquals(0, CommunicationRecord::count());

        RecordCreateService::handler($model);

        /** @var $rec CommunicationRecord */
        $rec = CommunicationRecord::first();

        $this->assertEquals($rec->client_id, $client->id);
        $this->assertNull($rec->client_ids);
        $this->assertNull($rec->division_id);
        $this->assertEquals($rec->type, Type::Inner);
        $this->assertNull($rec->channel_contact);
        $this->assertEquals($rec->sort_at, $model->created_at);

        $this->assertTrue($rec->is_answered);

        $this->assertInstanceOf(ConversationMark::class, $rec->entity);
        $this->assertEquals($rec->entity->id, $model->id);
    }

    /** @test */
    public function create_with_client_and_division()
    {
        /** @var $division Division */
        $division = $this->divisionBuilder->create();

        /** @var $client Client */
        $client = $this->clientBuilder->create();

        /** @var $model ConversationMark */
        $model = $this->conversationMarkBuilder
            ->client($client)
            ->create();

        $this->assertEquals(0, CommunicationRecord::count());

        RecordCreateService::handler($model, ['division_id' => $division->id]);

        /** @var $rec CommunicationRecord */
        $rec = CommunicationRecord::first();

        $this->assertEquals($rec->division_id, $division->id);
    }

    /** @test */
    public function create_with_client_as_has_phone()
    {
        $phone = '899807576';

        /** @var $client Client */
        $client = $this->clientBuilder->create();
        $this->phoneBuilder->client($client)
            ->value($phone)->create();

        /** @var $model ConversationMark */
        $model = $this->conversationMarkBuilder
            ->client(null)
            ->contact_type(ConversationContactType::Phone->value)
            ->contact_value($phone)
            ->create();

        $this->assertEquals(0, CommunicationRecord::count());

        RecordCreateService::handler($model);

        /** @var $rec CommunicationRecord */
        $rec = CommunicationRecord::first();

        $this->assertEquals($rec->client_id, $client->id);
        $this->assertNull($rec->client_ids);
        $this->assertEquals($rec->channel_contact, $phone);
    }

    /** @test */
    public function create_with_client_as_has_phones()
    {
        $phone = '899807576';

        /** @var $client Client */
        $client = $this->clientBuilder->create();
        $client_2 = $this->clientBuilder->create();
        $client_3 = $this->clientBuilder->create();

        $this->phoneBuilder->client($client)
            ->value($phone)->create();
        $this->phoneBuilder->client($client_2)
            ->value($phone)->create();
        $this->phoneBuilder->client($client_3)
            ->value($phone)->create();

        /** @var $model ConversationMark */
        $model = $this->conversationMarkBuilder
            ->client(null)
            ->contact_type(ConversationContactType::Phone->value)
            ->contact_value($phone)
            ->create();

        $this->assertEquals(0, CommunicationRecord::count());

        RecordCreateService::handler($model);

        /** @var $rec CommunicationRecord */
        $rec = CommunicationRecord::first();

        $this->assertEquals($rec->client_id, $client->id);
        $this->assertEquals($rec->client_ids, [
            $client_2->id,
            $client_3->id,
        ]);
        $this->assertEquals($rec->channel_contact, $phone);
    }

    /** @test */
    public function create_with_client_as_has_email()
    {
        $email = 'test@gmail.com';

        /** @var $client Client */
        $client = $this->clientBuilder->create();
        $this->emailBuilder->client($client)
            ->value($email)->create();

        /** @var $model ConversationMark */
        $model = $this->conversationMarkBuilder
            ->client(null)
            ->contact_type(ConversationContactType::Email->value)
            ->contact_value($email)
            ->create();

        $this->assertEquals(0, CommunicationRecord::count());

        RecordCreateService::handler($model);

        /** @var $rec CommunicationRecord */
        $rec = CommunicationRecord::first();

        $this->assertEquals($rec->client_id, $client->id);
        $this->assertNull($rec->client_ids);
        $this->assertEquals($rec->channel_contact, $email);
    }

    /** @test */
    public function create_with_client_as_has_emails()
    {
        $email = 'test@gmail.com';

        /** @var $client Client */
        $client = $this->clientBuilder->create();
        $client_2 = $this->clientBuilder->create();
        $client_3 = $this->clientBuilder->create();

        $this->emailBuilder->client($client)
            ->value($email)->create();
        $this->emailBuilder->client($client_2)
            ->value($email)->create();
        $this->emailBuilder->client($client_3)
            ->value($email)->create();

        /** @var $model ConversationMark */
        $model = $this->conversationMarkBuilder
            ->client(null)
            ->contact_type(ConversationContactType::Email->value)
            ->contact_value($email)
            ->create();

        $this->assertEquals(0, CommunicationRecord::count());

        RecordCreateService::handler($model);

        /** @var $rec CommunicationRecord */
        $rec = CommunicationRecord::first();

        $this->assertEquals($rec->client_id, $client->id);
        $this->assertEquals($rec->client_ids, [
            $client_2->id,
            $client_3->id,
        ]);
        $this->assertEquals($rec->channel_contact, $email);
    }

    /** @test */
    public function create_without_client()
    {
        /** @var $model ConversationMark */
        $model = $this->conversationMarkBuilder
            ->client(null)
            ->create();

        $this->assertEquals(0, CommunicationRecord::count());

        RecordCreateService::handler($model);

        /** @var $rec CommunicationRecord */
        $rec = CommunicationRecord::first();

        $this->assertNull($rec->client_id);
        $this->assertNull($rec->client_ids);
    }
}
