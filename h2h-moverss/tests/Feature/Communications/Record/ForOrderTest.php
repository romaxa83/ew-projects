<?php

namespace Tests\Feature\Communications\Record;

use App\Enums\Common\DateFormat;
use App\Models\Client;
use App\Models\Client\Email;
use App\Models\Client\Phone;
use App\Models\Division;
use App\Models\Order;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Clients\ActivityBuilder;
use Tests\Builders\Clients\ClientBuilder;
use Tests\Builders\Clients\EmailBuilder;
use Tests\Builders\Clients\PhoneBuilder;
use Tests\Builders\Communications\CommunicationRecordBuilder;
use Tests\Builders\Communications\ConversationMarkBuilder;
use Tests\Builders\Divisions\DivisionBuilder;
use Tests\Builders\Employees\EmployeeBuilder;
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

class ForOrderTest extends TestCase
{
    use DatabaseTransactions;

    protected DivisionBuilder $divisionBuilder;
    protected UserBuilder $userBuilder;
    protected EmployeeBuilder $employeeBuilder;
    protected TwilioSmsBuilder $twilioSmsBuilder;
    protected ClientBuilder $clientBuilder;
    protected PhoneBuilder $phoneBuilder;
    protected EmailBuilder $emailBuilder;
    protected ActivityBuilder $activityBuilder;
    protected EventAfterCallBuilder $ringostatBuilder;
    protected SmsEventBuilder $zadarmaSmsBuilder;
    protected CallEventBuilder $zadarmaCallBuilder;
    protected MessageBuilder $messageBuilder;
    protected ConversationMarkBuilder $conversationMarkBuilder;
    protected OrderBuilder $orderBuilder;
    protected NoteBuilder $orderNoteBuilder;
    protected \Tests\Builders\Orders\ActivityBuilder $orderActivityBuilder;
    protected TaskBuilder $taskBuilder;

    protected CommunicationRecordBuilder $communicationRecordBuilder;

    protected array $data;

    public function setUp(): void
    {
        $this->divisionBuilder = resolve(DivisionBuilder::class);
        $this->userBuilder = resolve(UserBuilder::class);
        $this->employeeBuilder = resolve(EmployeeBuilder::class);
        $this->twilioSmsBuilder = resolve(TwilioSmsBuilder::class);
        $this->clientBuilder = resolve(ClientBuilder::class);
        $this->phoneBuilder = resolve(PhoneBuilder::class);
        $this->emailBuilder = resolve(EmailBuilder::class);
        $this->activityBuilder = resolve(ActivityBuilder::class);
        $this->ringostatBuilder = resolve(EventAfterCallBuilder::class);
        $this->zadarmaSmsBuilder = resolve(SmsEventBuilder::class);
        $this->zadarmaCallBuilder = resolve(CallEventBuilder::class);
        $this->messageBuilder = resolve(MessageBuilder::class);
        $this->conversationMarkBuilder = resolve(ConversationMarkBuilder::class);
        $this->orderBuilder = resolve(OrderBuilder::class);
        $this->orderNoteBuilder = resolve(NoteBuilder::class);
        $this->orderActivityBuilder = resolve(\Tests\Builders\Orders\ActivityBuilder::class);
        $this->taskBuilder = resolve(TaskBuilder::class);
        $this->communicationRecordBuilder = resolve(CommunicationRecordBuilder::class);

        $this->data = [
            'historyTill' => null,
            'orderID' => null
        ];

        parent::setUp();
    }

    /** @test */
    public function success()
    {
        $this->loginUser();

        $date = CarbonImmutable::now();
        CarbonImmutable::setTestNow($date);

        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $this->session(['division' => $division->toArray()]);

        /** @var $client Client */
        $client = $this->clientBuilder->create();
        /** @var $phone Phone */
        $phone = $this->phoneBuilder->client($client)->create();

        /** @var $order Order */
        $order = $this->orderBuilder
            ->client($client)->create();

        $twilio = $this->twilioSmsBuilder->create();
        $rec_1 = $this->communicationRecordBuilder
            ->entity($twilio)
            ->channel_contact($phone->value)
            ->division($division)
            ->sort_at($date->subMinutes(4))
            ->create();

        $task = $this->taskBuilder->create();
        $rec_2 = $this->communicationRecordBuilder
            ->entity($task)
            ->order($order)
            ->division($division)
            ->sort_at($date->subMinutes(3))
            ->create();

        $data = $this->data;
        $data['orderID'] = $order->id;

        $dateFrom = (new CarbonImmutable($order->created_at, config('app.timezone')))
            ->modify("-2 day midnight")
            ->setTimezone('UTC')
        ;

        $this->post(route('orders.v2.records-for-order'), $data)
            ->assertJson([
                'success' => true,
                'data' => [
                    'pinnedNotes' => [],
                    'more' => false,
                    'records' => [
                        ['id' => $rec_2->id],
                        ['id' => $rec_1->id],
                    ],
                    'recordsTill' => $dateFrom->getTimestamp(),
                    'dateFrom' => $dateFrom->format(DateFormat::ISO_8601()),
                    'dateTill' => CarbonImmutable::now('UTC')->addDay()
                        ->format(DateFormat::ISO_8601())
                ]
            ])
            ->assertJsonCount(2, 'data.records')
            ->assertJsonCount(0, 'data.pinnedNotes')
        ;
    }

    /** @test */
    public function success_with_pinned_notes()
    {
        $this->loginUser();

        $date = CarbonImmutable::now();

        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $this->session(['division' => $division->toArray()]);

        /** @var $order Order */
        $order = $this->orderBuilder->create();

        $note_1 = $this->orderNoteBuilder->order($order)
            ->is_pinned(true)->create();
        $rec_1 = $this->communicationRecordBuilder
            ->entity($note_1)
            ->order($order)
            ->division($division)
            ->sort_at($date->subMinutes(10))
            ->create();

        $note_2 = $this->orderNoteBuilder->order($order)
            ->is_pinned(false)->create();
        $rec_2 = $this->communicationRecordBuilder
            ->entity($note_2)
            ->order($order)
            ->division($division)
            ->sort_at($date->subMinutes(9))
            ->create();

        $note_3 = $this->orderNoteBuilder->order($order)
            ->is_pinned(true)->create();
        $rec_3 = $this->communicationRecordBuilder
            ->entity($note_3)
            ->order($order)
            ->division($division)
            ->sort_at($date->subMinutes(8))
            ->create();

        $data = $this->data;
        $data['orderID'] = $order->id;

        $this->post(route('orders.v2.records-for-order'), $data)
            ->assertJson([
                'success' => true,
                'data' => [
                    'pinnedNotes' => [
                        ['id' => $rec_1->id],
                        ['id' => $rec_3->id]
                    ],
                    'more' => false,
                    'records' => [
                        ['id' => $rec_3->id],
                        ['id' => $rec_2->id],
                        ['id' => $rec_1->id],
                    ]
                ]
            ])
            ->assertJsonCount(3, 'data.records')
            ->assertJsonCount(2, 'data.pinnedNotes')
        ;
    }

    /** @test */
    public function success_without_order()
    {
        $this->loginUser();

        $date = CarbonImmutable::now();

        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $this->session(['division' => $division->toArray()]);

        /** @var $order Order */
        $order = $this->orderBuilder->create();

        $rec_1 = $this->communicationRecordBuilder
            ->entity($order)
            ->order($order)
            ->division($division)
            ->sort_at($date->subMinutes(10))
            ->create();

        $note_2 = $this->orderNoteBuilder->order($order)
            ->is_pinned(false)->create();
        $rec_2 = $this->communicationRecordBuilder
            ->entity($note_2)
            ->order($order)
            ->division($division)
            ->sort_at($date->subMinutes(9))
            ->create();

        $note_3 = $this->orderNoteBuilder->order($order)
            ->is_pinned(true)->create();
        $rec_3 = $this->communicationRecordBuilder
            ->entity($note_3)
            ->order($order)
            ->division($division)
            ->sort_at($date->subMinutes(8))
            ->create();

        $data = $this->data;
        $data['orderID'] = $order->id;

        $this->post(route('orders.v2.records-for-order'), $data)
            ->assertJson([
                'success' => true,
                'data' => [
                    'more' => false,
                    'records' => [
                        ['id' => $rec_3->id],
                        ['id' => $rec_2->id],
                    ]
                ]
            ])
            ->assertJsonCount(2, 'data.records')
        ;
    }

    /** @test */
    public function success_with_conversation_mark()
    {
        $this->loginUser();

        $date = CarbonImmutable::now();

        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $this->session(['division' => $division->toArray()]);

        /** @var $client Client */
        $client = $this->clientBuilder->create();
        $client_2 = $this->clientBuilder->create();

        /** @var $order Order */
        $order = $this->orderBuilder->client($client)->create();

        $conversation_1 = $this->conversationMarkBuilder
            ->create();
        $rec_1 = $this->communicationRecordBuilder
            ->entity($conversation_1)
            ->client($client)
            ->division($division)
            ->sort_at($date->subMinutes(10))
            ->create();

        $conversation_2 = $this->conversationMarkBuilder
            ->create();
        $rec_2 = $this->communicationRecordBuilder
            ->entity($conversation_2)
            ->client($client_2)
            ->division($division)
            ->sort_at($date->subMinutes(9))
            ->create();

        $note_3 = $this->orderNoteBuilder->order($order)
            ->is_pinned(true)->create();
        $rec_3 = $this->communicationRecordBuilder
            ->entity($note_3)
            ->order($order)
            ->division($division)
            ->sort_at($date->subMinutes(8))
            ->create();

        $data = $this->data;
        $data['orderID'] = $order->id;

        $this->post(route('orders.v2.records-for-order'), $data)
            ->assertJson([
                'success' => true,
                'data' => [
                    'more' => false,
                    'records' => [
                        ['id' => $rec_3->id],
                        ['id' => $rec_1->id],
                    ]
                ]
            ])
            ->assertJsonCount(2, 'data.records')
        ;
    }

    /** @test */
    public function success_with_conversation_mark_as_channel_contact()
    {
        $this->loginUser();

        $date = CarbonImmutable::now();

        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $this->session(['division' => $division->toArray()]);

        /** @var $client Client */
        $client = $this->clientBuilder->create();
        /** @var $email Email */
        $email = $this->emailBuilder->client($client)->create();

        /** @var $order Order */
        $order = $this->orderBuilder->client($client)->create();

        $conversation_1 = $this->conversationMarkBuilder
            ->create();
        $rec_1 = $this->communicationRecordBuilder
            ->entity($conversation_1)
            ->client(null)
            ->channel_contact($email->value)
            ->division($division)
            ->sort_at($date->subMinutes(10))
            ->create();


        $note_3 = $this->orderNoteBuilder->order($order)
            ->is_pinned(true)->create();
        $rec_3 = $this->communicationRecordBuilder
            ->entity($note_3)
            ->order($order)
            ->division($division)
            ->sort_at($date->subMinutes(8))
            ->create();

        $data = $this->data;
        $data['orderID'] = $order->id;

        $this->post(route('orders.v2.records-for-order'), $data)
            ->assertJson([
                'success' => true,
                'data' => [
                    'more' => false,
                    'records' => [
                        ['id' => $rec_3->id],
                        ['id' => $rec_1->id],
                    ]
                ]
            ])
            ->assertJsonCount(2, 'data.records')
        ;
    }

    /** @test */
    public function success_empty_data()
    {
        $this->loginUser();

        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $this->session(['division' => $division->toArray()]);

        /** @var $order Order */
        $order = $this->orderBuilder->create();

        $data = $this->data;
        $data['orderID'] = $order->id;

        $this->post(route('orders.v2.records-for-order'), $data)
            ->assertJson([
               'success' => true,
                'data' => [
                    'pinnedNotes' => [],
                    'more' => false,
                    'records' => []
                ]
            ])
            ->assertJsonCount(0, 'data.records')
            ->assertJsonCount(0, 'data.pinnedNotes')
        ;
    }
}
