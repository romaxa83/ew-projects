<?php

namespace Tests\Feature\Communications\Record;

use App\Models\Client;
use App\Models\Division;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Clients\ActivityBuilder;
use Tests\Builders\Clients\ClientBuilder;
use Tests\Builders\Communications\CommunicationRecordBuilder;
use Tests\Builders\Communications\ConversationMarkBuilder;
use Tests\Builders\Divisions\DivisionBuilder;
use Tests\Builders\Gmail\MessageBuilder;
use Tests\Builders\Orders\NoteBuilder;
use Tests\Builders\Orders\OrderBuilder;
use Tests\Builders\Ringostat\EventAfterCallBuilder;
use Tests\Builders\Tasks\TaskBuilder;
use Tests\Builders\Twilio\TwilioSmsBuilder;
use Tests\Builders\Users\UserBuilder;
use Tests\Builders\Zadarma\CallEventBuilder;
use Tests\Builders\Zadarma\SmsEventBuilder;
use Tests\TestCase;

class FlowTest extends TestCase
{
    use DatabaseTransactions;

    protected DivisionBuilder $divisionBuilder;
    protected UserBuilder $userBuilder;
    protected TwilioSmsBuilder $twilioSmsBuilder;
    protected ClientBuilder $clientBuilder;
    protected ActivityBuilder $activityBuilder;
    protected EventAfterCallBuilder $ringostatBuilder;
    protected SmsEventBuilder $zadarmaSmsBuilder;
    protected CallEventBuilder $zadarmaCallBuilder;
    protected MessageBuilder $messageBuilder;
    protected TaskBuilder $taskBuilder;
    protected NoteBuilder $orderNoteBuilder;
    protected ConversationMarkBuilder $conversationMarkBuilder;
    protected OrderBuilder $orderBuilder;

    protected CommunicationRecordBuilder $communicationRecordBuilder;

    protected array $data;

    public function setUp(): void
    {
        $this->divisionBuilder = resolve(DivisionBuilder::class);
        $this->userBuilder = resolve(UserBuilder::class);
        $this->twilioSmsBuilder = resolve(TwilioSmsBuilder::class);
        $this->clientBuilder = resolve(ClientBuilder::class);
        $this->activityBuilder = resolve(ActivityBuilder::class);
        $this->ringostatBuilder = resolve(EventAfterCallBuilder::class);
        $this->zadarmaSmsBuilder = resolve(SmsEventBuilder::class);
        $this->zadarmaCallBuilder = resolve(CallEventBuilder::class);
        $this->messageBuilder = resolve(MessageBuilder::class);
        $this->conversationMarkBuilder = resolve(ConversationMarkBuilder::class);
        $this->orderBuilder = resolve(OrderBuilder::class);
        $this->communicationRecordBuilder = resolve(CommunicationRecordBuilder::class);
        $this->taskBuilder = resolve(TaskBuilder::class);
        $this->orderNoteBuilder = resolve(NoteBuilder::class);

        $this->data = [
            'untill' => null,
            'contact' => [
                'client' => null,
            ]
        ];

        parent::setUp();
    }

    /** @test */
    public function success()
    {
        $this->loginUser();

        $date = CarbonImmutable::now();

        /** @var $client_1 Client */
        $client_1 = $this->clientBuilder->create();
        $client_2 = $this->clientBuilder->create();

        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $this->session(['division' => $division->toArray()]);

        $rec_1 = $this->communicationRecordBuilder
            ->division($division)
            ->client($client_1)
            ->sort_at($date->subMinutes(4))
            ->create();
        $rec_2 = $this->communicationRecordBuilder
            ->division($division)
            ->client($client_1)
            ->sort_at($date->subMinutes(3))
            ->create();
        $rec_3 = $this->communicationRecordBuilder
            ->division($division)
            ->client($client_1)
            ->sort_at($date->subMinutes(2))
            ->create();

        $rec_4 = $this->communicationRecordBuilder
            ->division($division)
            ->client($client_2)
            ->sort_at($date->subMinutes(1))
            ->create();

        $data = $this->data;
        $data['contact']['client'] = $client_1->toArray();


        $this->post(route('communications.v2.flow'), $data)
            ->assertJson([
                'success' => true,
                'more' => false,
                'page' => 1,
                'timezone' => $division->miscs['tz'],
                'records' => [
                    ['id' => $rec_3->id],
                    ['id' => $rec_2->id],
                    ['id' => $rec_1->id],
                ]
            ])
            ->assertJsonCount(3, 'records')
        ;
    }

    /** @test */
    public function success_without_client()
    {
        $this->loginUser();

        $date = CarbonImmutable::now();

        $channelContact_1 = '111111';
        $channelContact_2 = '211111';

        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $this->session(['division' => $division->toArray()]);

        $rec_1 = $this->communicationRecordBuilder
            ->division($division)
            ->client(null)
            ->channel_contact($channelContact_1)
            ->sort_at($date->subMinutes(4))
            ->create();
        $rec_2 = $this->communicationRecordBuilder
            ->division($division)
            ->client(null)
            ->channel_contact($channelContact_2)
            ->sort_at($date->subMinutes(3))
            ->create();
        $rec_3 = $this->communicationRecordBuilder
            ->division($division)
            ->client(null)
            ->channel_contact($channelContact_1)
            ->sort_at($date->subMinutes(2))
            ->create();

        $data = $this->data;
        $data['contact']['client'] = null;
        $data['contact']['channelContact'] = $channelContact_1;


        $this->post(route('communications.v2.flow'), $data)
            ->assertJson([
                'success' => true,
                'more' => false,
                'page' => 1,
                'timezone' => $division->miscs['tz'],
                'records' => [
                    ['id' => $rec_3->id],
                    ['id' => $rec_1->id],
                ]
            ])
            ->assertJsonCount(2, 'records')
        ;
    }

    /** @test */
    public function success_per_page()
    {
        $this->loginUser();

        $date = CarbonImmutable::now();

        /** @var $client_1 Client */
        $client_1 = $this->clientBuilder->create();
        $client_2 = $this->clientBuilder->create();

        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $this->session(['division' => $division->toArray()]);

        $rec_1 = $this->communicationRecordBuilder
            ->division($division)
            ->client($client_1)
            ->sort_at($date->subMinutes(4))
            ->create();
        $rec_2 = $this->communicationRecordBuilder
            ->division($division)
            ->client($client_1)
            ->sort_at($date->subMinutes(3))
            ->create();
        $rec_3 = $this->communicationRecordBuilder
            ->division($division)
            ->client($client_1)
            ->sort_at($date->subMinutes(2))
            ->create();

        $rec_4 = $this->communicationRecordBuilder
            ->division($division)
            ->client($client_2)
            ->sort_at($date->subMinutes(1))
            ->create();

        $data = $this->data;
        $data['contact']['client'] = $client_1->toArray();
        $data['per_page'] = 2;

        $this->post(route('communications.v2.flow'), $data)
            ->assertJson([
                'success' => true,
                'more' => true,
                'page' => 1,
                'timezone' => $division->miscs['tz'],
                'records' => [
                    ['id' => $rec_3->id],
                    ['id' => $rec_2->id],
                ]
            ])
            ->assertJsonCount(2, 'records')
        ;
    }

    /** @test */
    public function success_page()
    {
        $this->loginUser();

        $date = CarbonImmutable::now();

        /** @var $client_1 Client */
        $client_1 = $this->clientBuilder->create();
        $client_2 = $this->clientBuilder->create();

        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $this->session(['division' => $division->toArray()]);

        $rec_1 = $this->communicationRecordBuilder
            ->division($division)
            ->client($client_1)
            ->sort_at($date->subMinutes(4))
            ->create();
        $rec_2 = $this->communicationRecordBuilder
            ->division($division)
            ->client($client_1)
            ->sort_at($date->subMinutes(3))
            ->create();
        $rec_3 = $this->communicationRecordBuilder
            ->division($division)
            ->client($client_1)
            ->sort_at($date->subMinutes(2))
            ->create();

        $rec_4 = $this->communicationRecordBuilder
            ->division($division)
            ->client($client_2)
            ->sort_at($date->subMinutes(1))
            ->create();

        $data = $this->data;
        $data['contact']['client'] = $client_1->toArray();
        $data['per_page'] = 2;
        $data['page'] = 2;

        $this->post(route('communications.v2.flow'), $data)
            ->assertJson([
                'success' => true,
                'more' => false,
                'page' => 2,
                'timezone' => $division->miscs['tz'],
                'records' => [
                    ['id' => $rec_1->id],
                ]
            ])
            ->assertJsonCount(1, 'records')
        ;
    }


    /** @test */
    public function success_only_support_rec_type()
    {
        $this->loginUser();

        $date = CarbonImmutable::now();

        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $this->session(['division' => $division->toArray()]);

        /** @var $client Client */
        $client = $this->clientBuilder->create();

        $rec_1 = $this->communicationRecordBuilder
            ->division($division)
            ->client($client)
            ->entity($this->twilioSmsBuilder->create())
            ->sort_at($date->subMinutes(2))
            ->create();
        $rec_2 = $this->communicationRecordBuilder
            ->division($division)
            ->client($client)
            ->entity($this->activityBuilder->create())
            ->sort_at($date->subMinutes(3))
            ->create();
        $rec_3 = $this->communicationRecordBuilder
            ->division($division)
            ->client($client)
            ->entity($this->ringostatBuilder->create())
            ->sort_at($date->subMinutes(4))
            ->create();
        $rec_4 = $this->communicationRecordBuilder
            ->division($division)
            ->client($client)
            ->entity($this->zadarmaSmsBuilder->create())
            ->sort_at($date->subMinutes(5))
            ->create();
        $rec_5 = $this->communicationRecordBuilder
            ->division($division)
            ->client($client)
            ->entity($this->zadarmaCallBuilder->create())
            ->sort_at($date->subMinutes(6))
            ->create();
        $rec_6 = $this->communicationRecordBuilder
            ->division($division)
            ->client($client)
            ->entity($this->messageBuilder->create())
            ->sort_at($date->subMinutes(7))
            ->create();
        $rec_7 = $this->communicationRecordBuilder
            ->division($division)
            ->client($client)
            ->entity($this->conversationMarkBuilder->create())
            ->sort_at($date->subMinutes(8))
            ->create();
        $rec_8 = $this->communicationRecordBuilder
            ->division($division)
            ->client($client)
            ->entity($this->orderBuilder->create())
            ->sort_at($date->subMinutes(9))
            ->create();
        $rec_9 = $this->communicationRecordBuilder
            ->division($division)
            ->client($client)
            ->entity($this->taskBuilder->create())
            ->sort_at($date->subMinutes(8))
            ->create();
        $rec_10 = $this->communicationRecordBuilder
            ->division($division)
            ->client($client)
            ->entity($this->orderNoteBuilder->create())
            ->sort_at($date->subMinutes(7))
            ->create();

        $data = $this->data;
        $data['contact']['client'] = $client->toArray();

        $this->post(route('communications.v2.flow'), $data)
            ->assertJson([
                'records' => [
                    ['id' => $rec_1->id],
                    ['id' => $rec_2->id],
                    ['id' => $rec_3->id],
                    ['id' => $rec_4->id],
                    ['id' => $rec_5->id],
                    ['id' => $rec_6->id],
                    ['id' => $rec_7->id],
                    ['id' => $rec_8->id],
                ]
            ])
            ->assertJsonCount(8, 'records')
        ;
    }

    /** @test */
    public function success_empty_data()
    {
        $this->loginUser();

        $since = new CarbonImmutable('2021-01-01', 'UTC');

        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $this->session(['division' => $division->toArray()]);

        /** @var $client Client */
        $client = $this->clientBuilder->create();

        $data = $this->data;
        $data['contact']['client'] = $client->toArray();

        $this->post(route('communications.v2.flow'), $data)
            ->assertJson([
                'more' => false,
                'page' => 1,
                'success' => true,
                'records' => []
            ])
            ->assertJsonCount(0, 'records')
        ;
    }
}
