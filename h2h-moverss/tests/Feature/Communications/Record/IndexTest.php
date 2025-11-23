<?php

namespace Tests\Feature\Communications\Record;

use App\Enums\Communications\Filter\EntityEnum;
use App\Enums\Communications\Filter\PeriodEnum;
use App\ModelFilters\Communications\RecordFilter;
use App\Models\Client;
use App\Models\Division;
use App\Models\Order;
use App\Models\Ringostat\EventAfterCall;
use App\Models\Twilio\TwilioSms;
use App\Models\Zadarma\CallsEvents;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Clients\ActivityBuilder;
use Tests\Builders\Clients\ClientBuilder;
use Tests\Builders\Clients\EmailBuilder;
use Tests\Builders\Clients\PhoneBuilder;
use Tests\Builders\Communications\CommunicationRecordBuilder;
use Tests\Builders\Communications\ConversationFavoritesBuilder;
use Tests\Builders\Communications\ConversationMarkBuilder;
use Tests\Builders\Divisions\DivisionBuilder;
use Tests\Builders\Employees\EmployeeBuilder;
use Tests\Builders\Gmail\MessageBuilder;
use Tests\Builders\Orders\OrderBuilder;
use Tests\Builders\Ringostat\EventAfterCallBuilder;
use Tests\Builders\Twilio\TwilioSmsBuilder;
use Tests\Builders\Users\UserBuilder;
use Tests\Builders\Zadarma\CallEventBuilder;
use Tests\Builders\Zadarma\SmsEventBuilder;
use Tests\TestCase;

class IndexTest extends TestCase
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
    protected ConversationFavoritesBuilder $conversationFavoritesBuilder;
    protected OrderBuilder $orderBuilder;

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
        $this->conversationFavoritesBuilder = resolve(ConversationFavoritesBuilder::class);
        $this->conversationMarkBuilder = resolve(ConversationMarkBuilder::class);
        $this->orderBuilder = resolve(OrderBuilder::class);
        $this->communicationRecordBuilder = resolve(CommunicationRecordBuilder::class);

        $this->data = [
            'filters' => [
                'channels' => [],
                'communications' => "all",
                'contacts' => "all",
                'ignoreList' => null,
                'period' => null,
                'responsible' => null,
                'searchTerm' => null,
                'starred' => "all",
                'untill' => null,
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
        $division_2 = $this->divisionBuilder->create();

        $this->session(['division' => $division->toArray()]);

        $rec_1 = $this->communicationRecordBuilder
            ->channel_contact('2323232321')
            ->division($division)
            ->sort_at($date->subMinutes(4))
            ->create();
        $rec_2 = $this->communicationRecordBuilder
            ->channel_contact('2323232322')
            ->division($division)
            ->client($client_1)
            ->sort_at($date->subMinutes(3))
            ->create();
        $rec_3 = $this->communicationRecordBuilder
            ->channel_contact('2323232323')
            ->division($division_2)
            ->client($client_2)
            ->sort_at($date->subMinutes(2))
            ->create();

        $this->post(route('communications.v2.records'), $this->data)
            ->assertJson([
                'more' => false,
                'page' => 1,
                'records' => [
                    ['id' => $rec_2->id],
                    ['id' => $rec_1->id],
                ]
            ])
            ->assertJsonCount(2, 'records')
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

        $rec_1 = $this->communicationRecordBuilder
            ->division($division)
            ->entity($this->twilioSmsBuilder->create())
            ->sort_at($date->subMinutes(2))
            ->create();
        $rec_2 = $this->communicationRecordBuilder
            ->division($division)
            ->entity($this->activityBuilder->create())
            ->sort_at($date->subMinutes(3))
            ->create();
        $rec_3 = $this->communicationRecordBuilder
            ->division($division)
            ->entity($this->ringostatBuilder->create())
            ->sort_at($date->subMinutes(4))
            ->create();
        $rec_4 = $this->communicationRecordBuilder
            ->division($division)
            ->entity($this->zadarmaSmsBuilder->create())
            ->sort_at($date->subMinutes(5))
            ->create();
        $rec_5 = $this->communicationRecordBuilder
            ->division($division)
            ->entity($this->zadarmaCallBuilder->create())
            ->sort_at($date->subMinutes(6))
            ->create();
        $rec_6 = $this->communicationRecordBuilder
            ->division($division)
            ->entity($this->messageBuilder->create())
            ->sort_at($date->subMinutes(7))
            ->create();
        $rec_7 = $this->communicationRecordBuilder
            ->division($division)
            ->entity($this->conversationMarkBuilder->create())
            ->sort_at($date->subMinutes(8))
            ->create();
        $rec_8 = $this->communicationRecordBuilder
            ->division($division)
            ->entity($this->orderBuilder->create())
            ->sort_at($date->subMinutes(9))
            ->create();

        $this->post(route('communications.v2.records'), $this->data)
            ->assertJson([
                'records' => [
                    ['id' => $rec_1->id],
                    ['id' => $rec_2->id],
                    ['id' => $rec_3->id],
                    ['id' => $rec_4->id],
                    ['id' => $rec_5->id],
                    ['id' => $rec_6->id],
                ]
            ])
            ->assertJsonCount(6, 'records')
        ;
    }

    /** @test */
    public function success_by_per_page()
    {
        $this->loginUser();

        $date = CarbonImmutable::now();

        /** @var $client_1 Client */
        $client_1 = $this->clientBuilder->create();
        $client_2 = $this->clientBuilder->create();
        $client_3 = $this->clientBuilder->create();

        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $this->session(['division' => $division->toArray()]);

        $rec_1 = $this->communicationRecordBuilder
            ->division($division)
            ->sort_at($date->subMinutes(1))
            ->create();
        $rec_2 = $this->communicationRecordBuilder
            ->division($division)
            ->client($client_1)
            ->sort_at($date->subMinutes(2))
            ->create();
        $rec_3 = $this->communicationRecordBuilder
            ->division($division)
            ->client($client_2)
            ->sort_at($date->subMinutes(3))
            ->create();
        $rec_4 = $this->communicationRecordBuilder
            ->division($division)
            ->client($client_3)
            ->sort_at($date->subMinutes(4))
            ->create();

        $data = $this->data;
        $data['per_page'] = 2;

        $this->post(route('communications.v2.records'), $data)
            ->assertJson([
                'more' => true,
                'page' => 1,
                'records' => [
                    ['id' => $rec_1->id],
                    ['id' => $rec_2->id],
                ]
            ])
            ->assertJsonCount(2, 'records')
        ;
    }

    /** @test */
    public function success_by_page()
    {
        $this->loginUser();

        $date = CarbonImmutable::now();

        /** @var $client_1 Client */
        $client_1 = $this->clientBuilder->create();
        $client_2 = $this->clientBuilder->create();
        $client_3 = $this->clientBuilder->create();

        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $this->session(['division' => $division->toArray()]);

        $rec_1 = $this->communicationRecordBuilder
            ->channel_contact('2323232321')
            ->division($division)
            ->sort_at($date->subMinutes(4))
            ->create();
        $rec_2 = $this->communicationRecordBuilder
            ->channel_contact('2323232322')
            ->division($division)
            ->client($client_1)
            ->sort_at($date->subMinutes(3))
            ->create();
        $rec_3 = $this->communicationRecordBuilder
            ->channel_contact('2323232323')
            ->division($division)
            ->client($client_2)
            ->sort_at($date->subMinutes(2))
            ->create();
        $rec_4 = $this->communicationRecordBuilder
            ->channel_contact('2323232324')
            ->division($division)
            ->client($client_3)
            ->sort_at($date->subMinutes(1))
            ->create();

        $data = $this->data;
        $data['per_page'] = 2;
        $data['page'] = 2;

        $this->post(route('communications.v2.records'), $data)
            ->assertJson([
                'more' => false,
                'page' => 2,
                'records' => [
                    ['id' => $rec_2->id],
                    ['id' => $rec_1->id],
                ]
            ])
            ->assertJsonCount(2, 'records')
        ;
    }

    /** @test */
    public function success_by_page_as_one()
    {
        $this->loginUser();

        $date = CarbonImmutable::now();

        /** @var $client_1 Client */
        $client_1 = $this->clientBuilder->create();
        $client_2 = $this->clientBuilder->create();
        $client_3 = $this->clientBuilder->create();

        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $this->session(['division' => $division->toArray()]);

        $rec_1 = $this->communicationRecordBuilder
            ->division($division)
            ->sort_at($date->subMinutes(1))
            ->create();
        $rec_2 = $this->communicationRecordBuilder
            ->division($division)
            ->client($client_1)
            ->sort_at($date->subMinutes(2))
            ->create();
        $rec_3 = $this->communicationRecordBuilder
            ->division($division)
            ->client($client_2)
            ->sort_at($date->subMinutes(3))
            ->create();
        $rec_4 = $this->communicationRecordBuilder
            ->division($division)
            ->client($client_3)
            ->sort_at($date->subMinutes(4))
            ->create();

        $data = $this->data;
        $data['per_page'] = 2;
        $data['page'] = 1;

        $this->post(route('communications.v2.records'), $data)
            ->assertJson([
                'records' => [
                    ['id' => $rec_1->id],
                    ['id' => $rec_2->id],
                ]
            ])
            ->assertJsonCount(2, 'records')
        ;
    }

    /** @test */
    public function success_as_subsequence_by_client()
    {
        $this->loginUser();

        $date = CarbonImmutable::now();

        /** @var $client Client */
        $client = $this->clientBuilder->create();

        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $this->session(['division' => $division->toArray()]);

        $rec_1 = $this->communicationRecordBuilder
            ->division($division)
            ->client($client)
            ->sort_at($date->subMinutes(4))
            ->create();
        $rec_2 = $this->communicationRecordBuilder
            ->division($division)
            ->client($client)
            ->sort_at($date->subMinutes(3))
            ->create();
        $rec_3 = $this->communicationRecordBuilder
            ->division($division)
            ->client($client)
            ->sort_at($date->subMinutes(5))
            ->create();

        $data = $this->data;

        $this->post(route('communications.v2.records'), $data)
            ->assertJson([
                'records' => [
                    ['id' => $rec_2->id],
                ]
            ])
            ->assertJsonCount(1, 'records')
        ;
    }

    /** @test */
    public function success_as_subsequence_by_client_if_diffrent_entities()
    {
        $this->loginUser();

        $date = CarbonImmutable::now();

        /** @var $client Client */
        $client = $this->clientBuilder->create();
        $client_1 = $this->clientBuilder->create();
        $client_2 = $this->clientBuilder->create();

        $twilio = $this->twilioSmsBuilder->create();
        $ringostat = $this->ringostatBuilder->create();
        $zadarmaSms = $this->zadarmaSmsBuilder->create();

        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $this->session(['division' => $division->toArray()]);

        $rec_1 = $this->communicationRecordBuilder
            ->entity($twilio)
            ->division($division)
            ->client($client)
            ->sort_at($date->subMinutes(4))
            ->create();
        $rec_2 = $this->communicationRecordBuilder
            ->entity($ringostat)
            ->division($division)
            ->client($client_1)
            ->sort_at($date->subMinutes(3))
            ->create();
        $rec_3 = $this->communicationRecordBuilder
            ->entity($zadarmaSms)
            ->division($division)
            ->client($client_2)
            ->sort_at($date->subMinutes(5))
            ->create();

        $data = $this->data;

        $this->post(route('communications.v2.records'), $data)
            ->assertJson([
                'records' => [
                    ['id' => $rec_2->id],
                    ['id' => $rec_1->id],
                    ['id' => $rec_3->id],
                ]
            ])
            ->assertJsonCount(3, 'records')
        ;
    }

    /** @test */
    public function success_as_subsequence_by_client_if_diffrent_entities_but_one_client()
    {
        $this->loginUser();

        $date = CarbonImmutable::now();

        /** @var $client Client */
        $client = $this->clientBuilder->create();

        $twilio = $this->twilioSmsBuilder->create();
        $ringostat = $this->ringostatBuilder->create();
        $zadarmaSms = $this->zadarmaSmsBuilder->create();

        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $this->session(['division' => $division->toArray()]);

        $rec_1 = $this->communicationRecordBuilder
            ->entity($twilio)
            ->division($division)
            ->client($client)
            ->sort_at($date->subMinutes(4))
            ->create();
        $rec_2 = $this->communicationRecordBuilder
            ->entity($ringostat)
            ->division($division)
            ->client($client)
            ->sort_at($date->subMinutes(3))
            ->create();
        $rec_3 = $this->communicationRecordBuilder
            ->entity($zadarmaSms)
            ->division($division)
            ->client($client)
            ->sort_at($date->subMinutes(5))
            ->create();

        $data = $this->data;

        $this->post(route('communications.v2.records'), $data)
            ->assertJson([
                'records' => [
                    ['id' => $rec_2->id],
                ]
            ])
            ->assertJsonCount(1, 'records')
        ;
    }

    /** @test */
    public function filter_by_communications_as_unanswered()
    {
        $this->loginUser();

        $date = CarbonImmutable::now();

        /** @var $division Division */
        $division = $this->divisionBuilder->create();

        $this->session(['division' => $division->toArray()]);

        /** @var $client_1 Client */
        $client_1 = $this->clientBuilder->create();
        $client_2 = $this->clientBuilder->create();

        $rec_1 = $this->communicationRecordBuilder
            ->division($division)
            ->sort_at($date->subMinutes(12))
            ->is_answered(false)
            ->create();

        $rec_2 = $this->communicationRecordBuilder
            ->division($division)
            ->sort_at($date->subMinutes(13))
            ->client($client_1)
            ->is_answered(true)
            ->create();

        $rec_3 = $this->communicationRecordBuilder
            ->division($division)
            ->sort_at($date->subMinutes(14))
            ->client($client_2)
            ->is_answered(false)
            ->create();

        $data = $this->data;
        $data['filters']['communications'] = 'unanswered';

        $this->post(route('communications.v2.records'), $data)
            ->assertJson([
                'records' => [
                    ['id' => $rec_1->id],
                    ['id' => $rec_3->id],
                ]
            ])
            ->assertJsonCount(2, 'records')
        ;
    }

    /** @test */
    public function filter_by_channel_as_twilio()
    {
        $this->loginUser();

        $date = CarbonImmutable::now();

        /** @var $division Division */
        $division = $this->divisionBuilder->create();

        $this->session(['division' => $division->toArray()]);

        /** @var $client_1 Client */
        $client_1 = $this->clientBuilder->create();
        $client_2 = $this->clientBuilder->create();
        $client_3 = $this->clientBuilder->create();
        $client_4 = $this->clientBuilder->create();

        $twilio = $this->twilioSmsBuilder->create();
        $ringostat = $this->ringostatBuilder->create();
        $zadarmaSms = $this->zadarmaSmsBuilder->create();
        $zadarmaCall = $this->zadarmaCallBuilder->create();
        $activity = $this->activityBuilder->create();

        $rec_1 = $this->communicationRecordBuilder
            ->division($division)
            ->sort_at($date->subMinutes(10))
            ->entity($activity)
            ->create();

        $rec_2 = $this->communicationRecordBuilder
            ->division($division)
            ->client($client_1)
            ->sort_at($date->subMinutes(11))
            ->entity($twilio)
            ->create();

        $rec_3 = $this->communicationRecordBuilder
            ->division($division)
            ->client($client_2)
            ->sort_at($date->subMinutes(12))
            ->entity($ringostat)
            ->create();

        $rec_4 = $this->communicationRecordBuilder
            ->division($division)
            ->client($client_3)
            ->sort_at($date->subMinutes(12))
            ->entity($zadarmaSms)
            ->create();

        $rec_5 = $this->communicationRecordBuilder
            ->division($division)
            ->client($client_4)
            ->sort_at($date->subMinutes(14))
            ->entity($zadarmaCall)
            ->create();

        $data = $this->data;
        $data['filters']['channels'] = [TwilioSms::MORPH_NAME];

        $this->post(route('communications.v2.records'), $data)
            ->assertJson([
                'records' => [
                    ['id' => $rec_2->id],
                ]
            ])
            ->assertJsonCount(1, 'records')
        ;
    }

    /** @test */
    public function filter_by_channel_as_ringostat()
    {
        $this->loginUser();

        $date = CarbonImmutable::now();

        /** @var $client_1 Client */
        $client_1 = $this->clientBuilder->create();
        $client_2 = $this->clientBuilder->create();
        $client_3 = $this->clientBuilder->create();
        $client_4 = $this->clientBuilder->create();

        /** @var $division Division */
        $division = $this->divisionBuilder->create();

        $this->session(['division' => $division->toArray()]);

        $twilio = $this->twilioSmsBuilder->create();
        $ringostat = $this->ringostatBuilder->create();
        $zadarmaSms = $this->zadarmaSmsBuilder->create();
        $zadarmaCall = $this->zadarmaCallBuilder->create();
        $activity = $this->activityBuilder->create();


        $rec_1 = $this->communicationRecordBuilder
            ->division($division)
            ->sort_at($date->subMinutes(10))
            ->entity($activity)
            ->create();

        $rec_2 = $this->communicationRecordBuilder
            ->division($division)
            ->client($client_1)
            ->sort_at($date->subMinutes(11))
            ->entity($twilio)
            ->create();

        $rec_3 = $this->communicationRecordBuilder
            ->division($division)
            ->client($client_2)
            ->sort_at($date->subMinutes(12))
            ->entity($ringostat)
            ->create();

        $rec_4 = $this->communicationRecordBuilder
            ->division($division)
            ->client($client_3)
            ->sort_at($date->subMinutes(13))
            ->entity($zadarmaSms)
            ->create();

        $rec_5 = $this->communicationRecordBuilder
            ->division($division)
            ->client($client_4)
            ->sort_at($date->subMinutes(14))
            ->entity($zadarmaCall)
            ->create();

        $data = $this->data;
        $data['filters']['channels'] = [EventAfterCall::MORPH_NAME];

        $this->post(route('communications.v2.records'), $data)
            ->assertJson([
                'records' => [
                    ['id' => $rec_3->id],
                ]
            ])
            ->assertJsonCount(1, 'records')
        ;
    }

    /** @test */
    public function filter_by_channel_as_zadarma()
    {
        $this->loginUser();

        $date = CarbonImmutable::now();

        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $this->session(['division' => $division->toArray()]);

        /** @var $client_1 Client */
        $client_1 = $this->clientBuilder->create();
        $client_2 = $this->clientBuilder->create();
        $client_3 = $this->clientBuilder->create();
        $client_4 = $this->clientBuilder->create();

        $twilio = $this->twilioSmsBuilder->create();
        $ringostat = $this->ringostatBuilder->create();
        $zadarmaSms = $this->zadarmaSmsBuilder->create();
        $zadarmaCall = $this->zadarmaCallBuilder->create();
        $activity = $this->activityBuilder->create();

        $rec_1 = $this->communicationRecordBuilder
            ->division($division)
            ->sort_at($date->subMinutes(10))
            ->entity($activity)
            ->create();

        $rec_2 = $this->communicationRecordBuilder
            ->division($division)
            ->client($client_1)
            ->sort_at($date->subMinutes(11))
            ->entity($twilio)
            ->create();

        $rec_3 = $this->communicationRecordBuilder
            ->division($division)
            ->client($client_2)
            ->sort_at($date->subMinutes(12))
            ->entity($ringostat)
            ->create();

        $rec_4 = $this->communicationRecordBuilder
            ->division($division)
            ->client($client_3)
            ->sort_at($date->subMinutes(13))
            ->entity($zadarmaSms)
            ->create();

        $rec_5 = $this->communicationRecordBuilder
            ->division($division)
            ->client($client_4)
            ->sort_at($date->subMinutes(14))
            ->entity($zadarmaCall)
            ->create();

        $data = $this->data;
        $data['filters']['channels'] = [
            CallsEvents::MORPH_NAME,
        ];

        $this->post(route('communications.v2.records'), $data)
            ->assertJson([
                'records' => [
                    ['id' => $rec_4->id],
                    ['id' => $rec_5->id],
                ]
            ])
            ->assertJsonCount(2, 'records')
        ;
    }

    /** @test */
    public function filter_by_period_as_today()
    {
        $this->loginUser();

        $date = CarbonImmutable::now();

        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $this->session(['division' => $division->toArray()]);

        /** @var $client_1 Client */
        $client_1 = $this->clientBuilder->create();
        $client_2 = $this->clientBuilder->create();

        $twilio = $this->twilioSmsBuilder->create();
        $activity = $this->activityBuilder->create();

        $rec_1 = $this->communicationRecordBuilder
            ->division($division)
            ->sort_at($date->subMinutes(10))
            ->entity($activity)
            ->create();

        $rec_2 = $this->communicationRecordBuilder
            ->division($division)
            ->client($client_1)
            ->sort_at($date->subMinutes(11))
            ->entity($twilio)
            ->create();

        $rec_3 = $this->communicationRecordBuilder
            ->division($division)
            ->client($client_2)
            ->sort_at($date->subHours(40))
            ->entity($twilio)
            ->create();

        $data = $this->data;
        $data['filters']['period'] = PeriodEnum::Today();

        $this->post(route('communications.v2.records'), $data)
            ->assertJson([
                'records' => [
                    ['id' => $rec_1->id],
                    ['id' => $rec_2->id],
                ]
            ])
            ->assertJsonCount(2, 'records')
        ;
    }

    /** @test */
    public function filter_by_period_as_yesterday()
    {
        $this->loginUser();

        $date = CarbonImmutable::now();

        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $this->session(['division' => $division->toArray()]);

        /** @var $client_1 Client */
        $client_1 = $this->clientBuilder->create();
        $client_2 = $this->clientBuilder->create();

        $twilio = $this->twilioSmsBuilder->create();
        $activity = $this->activityBuilder->create();

        $rec_1 = $this->communicationRecordBuilder
            ->division($division)
            ->sort_at($date->subMinutes(10))
            ->entity($activity)
            ->create();

        $rec_2 = $this->communicationRecordBuilder
            ->division($division)
            ->client($client_1)
            ->sort_at($date->subHours(1))
            ->entity($twilio)
            ->create();

        $rec_3 = $this->communicationRecordBuilder
            ->division($division)
            ->client($client_2)
            ->sort_at($date->subDays(1))
            ->entity($twilio)
            ->create();

        $data = $this->data;
        $data['filters']['period'] = PeriodEnum::Yesterday();

        $this->post(route('communications.v2.records'), $data)
            ->assertJson([
                'records' => [
                    ['id' => $rec_3->id],
                ]
            ])
            ->assertJsonCount(1, 'records')
        ;
    }

    /** @test */
    public function filter_by_period_as_last_7_days()
    {
        $this->loginUser();

        $date = CarbonImmutable::now();

        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $this->session(['division' => $division->toArray()]);

        /** @var $client_1 Client */
        $client_1 = $this->clientBuilder->create();
        $client_2 = $this->clientBuilder->create();

        $twilio = $this->twilioSmsBuilder->create();
        $activity = $this->activityBuilder->create();

        $rec_1 = $this->communicationRecordBuilder
            ->division($division)
            ->sort_at($date->subMinutes(10))
            ->entity($activity)
            ->create();

        $rec_2 = $this->communicationRecordBuilder
            ->division($division)
            ->client($client_1)
            ->sort_at($date->subDays(5))
            ->entity($twilio)
            ->create();

        $rec_3 = $this->communicationRecordBuilder
            ->division($division)
            ->client($client_2)
            ->sort_at($date->subDays(10))
            ->entity($twilio)
            ->create();

        $data = $this->data;
        $data['filters']['period'] = PeriodEnum::Last_7_days();

        $this->post(route('communications.v2.records'), $data)
            ->assertJson([
                'records' => [
                    ['id' => $rec_1->id],
                    ['id' => $rec_2->id],
                ]
            ])
            ->assertJsonCount(2, 'records')
        ;
    }

    /** @test */
    public function filter_by_period_as_last_30_days()
    {
        $this->loginUser();

        $date = CarbonImmutable::now();

        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $this->session(['division' => $division->toArray()]);

        /** @var $client_1 Client */
        $client_1 = $this->clientBuilder->create();
        $client_2 = $this->clientBuilder->create();

        $twilio = $this->twilioSmsBuilder->create();
        $activity = $this->activityBuilder->create();

        $rec_1 = $this->communicationRecordBuilder
            ->division($division)
            ->sort_at($date->subMinutes(10))
            ->entity($activity)
            ->create();

        $rec_2 = $this->communicationRecordBuilder
            ->division($division)
            ->client($client_1)
            ->sort_at($date->subDays(50))
            ->entity($twilio)
            ->create();

        $rec_3 = $this->communicationRecordBuilder
            ->division($division)
            ->client($client_2)
            ->sort_at($date->subDays(10))
            ->entity($twilio)
            ->create();

        $data = $this->data;
        $data['filters']['period'] = PeriodEnum::Last_30_days();

        $this->post(route('communications.v2.records'), $data)
            ->assertJson([
                'records' => [
                    ['id' => $rec_1->id],
                    ['id' => $rec_3->id],
                ]
            ])
            ->assertJsonCount(2, 'records')
        ;
    }

    /** @test */
    public function filter_by_period_as_any()
    {
        $this->loginUser();

        $date = CarbonImmutable::now();

        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $this->session(['division' => $division->toArray()]);

        /** @var $client_1 Client */
        $client_1 = $this->clientBuilder->create();
        $client_2 = $this->clientBuilder->create();

        $twilio = $this->twilioSmsBuilder->create();
        $activity = $this->activityBuilder->create();

        $rec_1 = $this->communicationRecordBuilder
            ->division($division)
            ->sort_at($date->subMinutes(10))
            ->entity($activity)
            ->create();

        $rec_2 = $this->communicationRecordBuilder
            ->division($division)
            ->client($client_1)
            ->sort_at($date->subDays(50))
            ->entity($twilio)
            ->create();

        $rec_3 = $this->communicationRecordBuilder
            ->division($division)
            ->client($client_2)
            ->sort_at($date->subDays(10))
            ->entity($twilio)
            ->create();

        $data = $this->data;
        $data['filters']['period'] = PeriodEnum::Any();

        $this->post(route('communications.v2.records'), $data)
            ->assertJson([
                'records' => [
                    ['id' => $rec_1->id],
                    ['id' => $rec_3->id],
                    ['id' => $rec_2->id],
                ]
            ])
            ->assertJsonCount(3, 'records')
        ;
    }

    /** @test */
    public function filter_by_responsible_as_one()
    {
        $this->loginUser();

        $date = CarbonImmutable::now();

        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $this->session(['division' => $division->toArray()]);

        /** @var $client_1 Client */
        $client_1 = $this->clientBuilder->create();
        $user_1 = $this->userBuilder->create();
        $employee_1 = $this->employeeBuilder->user($user_1)->create();
        $order_1 = $this->orderBuilder
            ->manager($user_1)
            ->client($client_1)
            ->create();

        $client_2 = $this->clientBuilder->create();
        $user_2 = $this->userBuilder->create();
        $employee_2 = $this->employeeBuilder->user($user_2)->create();
        $order_2 = $this->orderBuilder
            ->manager($user_2)
            ->client($client_2)
            ->create();

        $twilio = $this->twilioSmsBuilder->create();
        $activity = $this->activityBuilder->create();

        $rec_1 = $this->communicationRecordBuilder
            ->division($division)
            ->sort_at($date->subMinutes(10))
            ->entity($activity)
            ->client($client_1)
            ->create();

        $rec_2 = $this->communicationRecordBuilder
            ->division($division)
            ->client($client_2)
            ->sort_at($date->subMinutes(11))
            ->entity($twilio)
            ->create();

        $rec_3 = $this->communicationRecordBuilder
            ->division($division)
            ->client($client_2)
            ->sort_at($date->subMinutes(12))
            ->entity($activity)
            ->create();

        $data = $this->data;
        $data['filters']['responsible'] = [
            $employee_1->id,
        ];

        $this->post(route('communications.v2.records'), $data)
            ->assertJson([
                'records' => [
                    ['id' => $rec_1->id],
                ]
            ])
            ->assertJsonCount(1, 'records')
        ;
    }

    /** @test */
    public function filter_by_responsible_as_more()
    {
        $this->loginUser();

        $date = CarbonImmutable::now();

        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $this->session(['division' => $division->toArray()]);

        /** @var $client_1 Client */
        $client_1 = $this->clientBuilder->create();
        $user_1 = $this->userBuilder->create();
        $employee_1 = $this->employeeBuilder->user($user_1)->create();
        $order_1 = $this->orderBuilder
            ->manager($user_1)
            ->client($client_1)
            ->create();

        $client_2 = $this->clientBuilder->create();
        $user_2 = $this->userBuilder->create();
        $employee_2 = $this->employeeBuilder->user($user_2)->create();
        $order_2 = $this->orderBuilder
            ->manager($user_2)
            ->client($client_2)
            ->create();

        $twilio = $this->twilioSmsBuilder->create();
        $activity = $this->activityBuilder->create();

        $rec_1 = $this->communicationRecordBuilder
            ->division($division)
            ->sort_at($date->subMinutes(10))
            ->entity($activity)
            ->client($client_1)
            ->create();

        $rec_2 = $this->communicationRecordBuilder
            ->division($division)
            ->client($client_2)
            ->sort_at($date->subMinutes(11))
            ->entity($twilio)
            ->create();

        $rec_3 = $this->communicationRecordBuilder
            ->division($division)
            ->client($client_2)
            ->sort_at($date->subMinutes(12))
            ->entity($activity)
            ->create();

        $data = $this->data;
        $data['filters']['responsible'] = [
            $employee_1->id,
            $employee_2->id,
        ];

        $this->post(route('communications.v2.records'), $data)
            ->assertJson([
                'records' => [
                    ['id' => $rec_1->id],
                    ['id' => $rec_2->id],
                ]
            ])
            ->assertJsonCount(2, 'records')
        ;
    }

    /** @test */
    public function filter_by_responsible_as_empty()
    {
        $this->loginUser();

        $date = CarbonImmutable::now();

        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $this->session(['division' => $division->toArray()]);

        /** @var $client_1 Client */
        $client_1 = $this->clientBuilder->create();
        $user_1 = $this->userBuilder->create();
        $employee_1 = $this->employeeBuilder->user($user_1)->create();
        $order_1 = $this->orderBuilder
            ->manager($user_1)
            ->client($client_1)
            ->create();

        $client_2 = $this->clientBuilder->create();
        $user_2 = $this->userBuilder->create();
        $employee_2 = $this->employeeBuilder->user($user_2)->create();
        $order_2 = $this->orderBuilder
            ->manager($user_2)
            ->client($client_2)
            ->create();

        $twilio = $this->twilioSmsBuilder->create();
        $activity = $this->activityBuilder->create();

        $rec_1 = $this->communicationRecordBuilder
            ->division($division)
            ->sort_at($date->subMinutes(10))
            ->entity($activity)
            ->client($client_1)
            ->create();

        $data = $this->data;
        $data['filters']['responsible'] = [
            $employee_2->id,
        ];

        $this->post(route('communications.v2.records'), $data)
            ->assertJsonCount(0, 'records')
        ;
    }

    /** @test */
    public function filter_by_contact_as_my_clients()
    {
        $user = $this->loginUser();

        $date = CarbonImmutable::now();

        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $this->session(['division' => $division->toArray()]);

        /** @var $client_1 Client */
        $client_1 = $this->clientBuilder->create();
        $client_2 = $this->clientBuilder->create();
        $client_3 = $this->clientBuilder->create();

        $user_1 = $this->userBuilder->create();

        $order_1 = $this->orderBuilder
            ->manager($user)
            ->client($client_1)
            ->create();

        $order_2 = $this->orderBuilder
            ->manager($user)
            ->client($client_2)
            ->create();

        $order_3 = $this->orderBuilder
            ->manager($user_1)
            ->client($client_3)
            ->create();

        $twilio = $this->twilioSmsBuilder->create();
        $activity = $this->activityBuilder->create();

        $rec_1 = $this->communicationRecordBuilder
            ->division($division)
            ->sort_at($date->subMinutes(10))
            ->entity($activity)
            ->client($client_1)
            ->create();

        $rec_2 = $this->communicationRecordBuilder
            ->division($division)
            ->sort_at($date->subMinutes(9))
            ->entity($twilio)
            ->client($client_2)
            ->create();

        $rec_3 = $this->communicationRecordBuilder
            ->division($division)
            ->sort_at($date->subMinutes(8))
            ->entity($activity)
            ->client($client_3)
            ->create();

        $data = $this->data;
        $data['filters']['contacts'] = RecordFilter::CONTACTS_MY_CLIENT;

        $this->post(route('communications.v2.records'), $data)
            ->assertJson([
                'records' => [
                    ['id' => $rec_2->id],
                    ['id' => $rec_1->id],
                ]
            ])
            ->assertJsonCount(2, 'records')
        ;
    }

    /** @test */
    public function filter_by_contact_as_unassigned()
    {
        $this->loginUser();

        $date = CarbonImmutable::now();

        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $this->session(['division' => $division->toArray()]);

        /** @var $client_1 Client */
        $client_1 = $this->clientBuilder->create();

        $twilio = $this->twilioSmsBuilder->create();
        $activity = $this->activityBuilder->create();

        $rec_1 = $this->communicationRecordBuilder
            ->division($division)
            ->sort_at($date->subMinutes(10))
            ->entity($activity)
            ->client($client_1)
            ->create();

        $rec_2 = $this->communicationRecordBuilder
            ->division($division)
            ->sort_at($date->subMinutes(9))
            ->entity($twilio)
            ->client(null)
            ->create();

        $rec_3 = $this->communicationRecordBuilder
            ->division($division)
            ->sort_at($date->subMinutes(8))
            ->entity($activity)
            ->client(null)
            ->create();

        $data = $this->data;
        $data['filters']['contacts'] = RecordFilter::CONTACTS_UNASSIGNED;

        $this->post(route('communications.v2.records'), $data)
            ->assertJson([
                'records' => [
                    ['id' => $rec_3->id],
                    ['id' => $rec_2->id],
                ]
            ])
            ->assertJsonCount(2, 'records')
        ;
    }

    /** @test */
    public function filter_by_starred_as_starred()
    {
        $user = $this->loginUser();

        $date = CarbonImmutable::now();

        $user_2 = $this->userBuilder->create();

        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $this->session(['division' => $division->toArray()]);

        /** @var $client_1 Client */
        $client_1 = $this->clientBuilder->create();

        $twilio = $this->twilioSmsBuilder->create();
        $activity = $this->activityBuilder->create();

        $rec_1 = $this->communicationRecordBuilder
            ->division($division)
            ->sort_at($date->subMinutes(10))
            ->entity($activity)
            ->client($client_1)
            ->create();
        $this->conversationFavoritesBuilder
            ->user($user)
            ->communication_rec($rec_1)
            ->starred(true)
            ->create();

        $rec_2 = $this->communicationRecordBuilder
            ->division($division)
            ->sort_at($date->subMinutes(9))
            ->entity($twilio)
            ->client(null)
            ->create();
        $this->conversationFavoritesBuilder
            ->user($user_2)
            ->communication_rec($rec_2)
            ->starred(true)
            ->create();

        $rec_3 = $this->communicationRecordBuilder
            ->division($division)
            ->sort_at($date->subMinutes(8))
            ->entity($activity)
            ->client(null)
            ->create();
        $this->conversationFavoritesBuilder
            ->user($user)
            ->communication_rec($rec_3)
            ->starred(true)
            ->create();

        $rec_4 = $this->communicationRecordBuilder
            ->division($division)
            ->sort_at($date->subMinutes(7))
            ->entity($activity)
            ->client(null)
            ->create();
        $this->conversationFavoritesBuilder
            ->user($user)
            ->communication_rec($rec_4)
            ->starred(false)
            ->create();

        $rec_5 = $this->communicationRecordBuilder
            ->division($division)
            ->sort_at($date->subMinutes(6))
            ->entity($activity)
            ->client(null)
            ->create();
        $this->conversationFavoritesBuilder
            ->user($user)
            ->communication_rec(null)
            ->starred(false)
            ->create();

        $data = $this->data;
        $data['filters']['starred'] = RecordFilter::STARRED_STARRED;

        $this->post(route('communications.v2.records'), $data)
            ->assertJson([
                'records' => [
                    ['id' => $rec_3->id],
                    ['id' => $rec_1->id],
                ]
            ])
            ->assertJsonCount(2, 'records')
        ;
    }

    /** @test */
    public function filter_by_starred_as_not_starred()
    {
        $user = $this->loginUser();

        $date = CarbonImmutable::now();

        $user_2 = $this->userBuilder->create();

        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $this->session(['division' => $division->toArray()]);

        /** @var $client_1 Client */
        $client_1 = $this->clientBuilder->create();

        $twilio = $this->twilioSmsBuilder->create();
        $activity = $this->activityBuilder->create();

        $rec_1 = $this->communicationRecordBuilder
            ->channel_contact('2323232321')
            ->division($division)
            ->sort_at($date->subMinutes(10))
            ->entity($activity)
            ->client($client_1)
            ->create();
        $this->conversationFavoritesBuilder
            ->user($user)
            ->communication_rec($rec_1)
            ->starred(true)
            ->create();

        $rec_2 = $this->communicationRecordBuilder
            ->channel_contact('2323232322')
            ->division($division)
            ->sort_at($date->subMinutes(9))
            ->entity($activity)
            ->client(null)
            ->create();
        $this->conversationFavoritesBuilder
            ->user($user_2)
            ->communication_rec($rec_2)
            ->starred(true)
            ->create();

        $rec_3 = $this->communicationRecordBuilder
            ->channel_contact('2323232323')
            ->division($division)
            ->sort_at($date->subMinutes(8))
            ->entity($activity)
            ->client(null)
            ->create();
        $this->conversationFavoritesBuilder
            ->user($user)
            ->communication_rec($rec_3)
            ->starred(true)
            ->create();

        $rec_4 = $this->communicationRecordBuilder
            ->channel_contact('2323232324')
            ->division($division)
            ->sort_at($date->subMinutes(7))
            ->entity($activity)
            ->client(null)
            ->create();
        $this->conversationFavoritesBuilder
            ->user($user)
            ->communication_rec($rec_4)
            ->starred(false)
            ->create();

        $rec_5 = $this->communicationRecordBuilder
            ->channel_contact('2323232325')
            ->division($division)
            ->sort_at($date->subMinutes(6))
            ->entity($activity)
            ->client(null)
            ->create();
        $this->conversationFavoritesBuilder
            ->user($user)
            ->communication_rec(null)
            ->starred(false)
            ->create();

        $data = $this->data;
        $data['filters']['starred'] = RecordFilter::STARRED_NOT_STARRED;

        $this->post(route('communications.v2.records'), $data)
            ->assertJson([
                'records' => [
                    ['id' => $rec_5->id],
                    ['id' => $rec_4->id],
                    ['id' => $rec_2->id],
                ]
            ])
            ->assertJsonCount(3, 'records')
        ;
    }

    /** @test */
    public function filter_by_entity_as_all()
    {
        $this->loginUser();

        $date = CarbonImmutable::now();

        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $this->session(['division' => $division->toArray()]);

        $twilio = $this->twilioSmsBuilder->create();
        $activity = $this->activityBuilder->create();
        $ringostat = $this->ringostatBuilder->create();
        $zadarmaCall = $this->zadarmaCallBuilder->create();
        $zadarmaSms = $this->zadarmaSmsBuilder->create();
        $email = $this->messageBuilder->create();

        $rec_1 = $this->communicationRecordBuilder
            ->division($division)
            ->sort_at($date->subMinutes(10))
            ->entity($activity)
            ->create();

        $rec_2 = $this->communicationRecordBuilder
            ->division($division)
            ->sort_at($date->subMinutes(9))
            ->entity($twilio)
            ->create();

        $rec_3 = $this->communicationRecordBuilder
            ->division($division)
            ->sort_at($date->subMinutes(8))
            ->entity($ringostat)
            ->create();

        $rec_4 = $this->communicationRecordBuilder
            ->division($division)
            ->sort_at($date->subMinutes(7))
            ->entity($zadarmaCall)
            ->create();

        $rec_5 = $this->communicationRecordBuilder
            ->division($division)
            ->sort_at($date->subMinutes(6))
            ->entity($zadarmaSms)
            ->create();

        $rec_6 = $this->communicationRecordBuilder
            ->division($division)
            ->sort_at($date->subMinutes(5))
            ->entity($email)
            ->create();

        $data = $this->data;
        $data['filters']['entity'] = EntityEnum::All();

        $this->post(route('communications.v2.records'), $data)
            ->assertJson([
                'records' => [
                    ['id' => $rec_6->id],
                    ['id' => $rec_5->id],
                    ['id' => $rec_4->id],
                    ['id' => $rec_3->id],
                    ['id' => $rec_2->id],
                    ['id' => $rec_1->id],
                ]
            ])
            ->assertJsonCount(6, 'records')
        ;
    }

    /** @test */
    public function filter_by_entity_as_call()
    {
        $this->loginUser();

        $date = CarbonImmutable::now();

        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $this->session(['division' => $division->toArray()]);

        $twilio = $this->twilioSmsBuilder->create();
        $activity = $this->activityBuilder->create();
        $ringostat = $this->ringostatBuilder->create();
        $zadarmaCall = $this->zadarmaCallBuilder->create();
        $zadarmaSms = $this->zadarmaSmsBuilder->create();
        $email = $this->messageBuilder->create();

        $rec_1 = $this->communicationRecordBuilder
            ->division($division)
            ->sort_at($date->subMinutes(10))
            ->entity($activity)
            ->create();

        $rec_2 = $this->communicationRecordBuilder
            ->division($division)
            ->sort_at($date->subMinutes(9))
            ->entity($twilio)
            ->create();

        $rec_3 = $this->communicationRecordBuilder
            ->division($division)
            ->sort_at($date->subMinutes(8))
            ->entity($ringostat)
            ->create();

        $rec_4 = $this->communicationRecordBuilder
            ->division($division)
            ->sort_at($date->subMinutes(7))
            ->entity($zadarmaCall)
            ->create();

        $rec_5 = $this->communicationRecordBuilder
            ->division($division)
            ->sort_at($date->subMinutes(6))
            ->entity($zadarmaSms)
            ->create();

        $rec_6 = $this->communicationRecordBuilder
            ->division($division)
            ->sort_at($date->subMinutes(5))
            ->entity($email)
            ->create();

        $data = $this->data;
        $data['filters']['entity'] = EntityEnum::Calls();

        $this->post(route('communications.v2.records'), $data)
            ->assertJson([
                'records' => [
                    ['id' => $rec_5->id],
                    ['id' => $rec_4->id],
                    ['id' => $rec_3->id],
                    ['id' => $rec_2->id],
                ]
            ])
            ->assertJsonCount(4, 'records')
        ;
    }

    /** @test */
    public function filter_by_entity_as_email()
    {
        $this->loginUser();

        $date = CarbonImmutable::now();

        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $this->session(['division' => $division->toArray()]);

        $twilio = $this->twilioSmsBuilder->create();
        $activity = $this->activityBuilder->create();
        $ringostat = $this->ringostatBuilder->create();
        $zadarmaCall = $this->zadarmaCallBuilder->create();
        $zadarmaSms = $this->zadarmaSmsBuilder->create();
        $email = $this->messageBuilder->create();

        $rec_1 = $this->communicationRecordBuilder
            ->division($division)
            ->sort_at($date->subMinutes(10))
            ->entity($activity)
            ->create();

        $rec_2 = $this->communicationRecordBuilder
            ->division($division)
            ->sort_at($date->subMinutes(9))
            ->entity($twilio)
            ->create();

        $rec_3 = $this->communicationRecordBuilder
            ->division($division)
            ->sort_at($date->subMinutes(8))
            ->entity($ringostat)
            ->create();

        $rec_4 = $this->communicationRecordBuilder
            ->division($division)
            ->sort_at($date->subMinutes(7))
            ->entity($zadarmaCall)
            ->create();

        $rec_5 = $this->communicationRecordBuilder
            ->division($division)
            ->sort_at($date->subMinutes(6))
            ->entity($zadarmaSms)
            ->create();

        $rec_6 = $this->communicationRecordBuilder
            ->division($division)
            ->sort_at($date->subMinutes(5))
            ->entity($email)
            ->create();

        $data = $this->data;
        $data['filters']['entity'] = EntityEnum::Emails();

        $this->post(route('communications.v2.records'), $data)
            ->assertJson([
                'records' => [
                    ['id' => $rec_6->id],
                ]
            ])
            ->assertJsonCount(1, 'records')
        ;
    }

    /** @test */
    public function search_client_by_name_and_email()
    {
        $this->loginUser();

        $date = CarbonImmutable::now();

        $value = 'aaaaaa';

        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $this->session(['division' => $division->toArray()]);

        /** @var $client_1 Client */
        $client_1 = $this->clientBuilder
            ->name($value)->lname('bbbbbbb')
            ->create();

        $client_2 = $this->clientBuilder
            ->name('zzzzz')->lname('zzzzzz')
            ->create();

        $client_3 = $this->clientBuilder
            ->name('bbbbbb')->lname('zzzz')
            ->create();
        $this->emailBuilder->value($value.'@gmail.com')->client($client_3)->create();

        $twilio = $this->twilioSmsBuilder->create();
        $activity = $this->activityBuilder->create();

        $rec_1 = $this->communicationRecordBuilder
            ->division($division)
            ->sort_at($date->subMinutes(10))
            ->entity($activity)
            ->client($client_1)
            ->create();

        $rec_2 = $this->communicationRecordBuilder
            ->division($division)
            ->sort_at($date->subMinutes(11))
            ->entity($activity)
            ->client($client_2)
            ->create();

        $rec_3 = $this->communicationRecordBuilder
            ->division($division)
            ->sort_at($date->subMinutes(12))
            ->entity($twilio)
            ->client($client_3)
            ->create();

        $data = $this->data;
        $data['filters']['searchTerm'] = $value;

        $this->post(route('communications.v2.records'), $data)
            ->assertJson([
                'records' => [
                    ['id' => $rec_1->id],
                    ['id' => $rec_3->id],
                ]
            ])
            ->assertJsonCount(2, 'records')
        ;
    }

    /** @test */
    public function search_client_by_name()
    {
        $this->loginUser();

        $date = CarbonImmutable::now();

        $value = 'aaaaaa';

        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $this->session(['division' => $division->toArray()]);

        /** @var $client_1 Client */
        $client_1 = $this->clientBuilder
            ->name($value)->lname('bbbbbbb')
            ->create();

        $client_2 = $this->clientBuilder
            ->name('zzzzz')->lname('zzzzzz')
            ->create();

        $client_3 = $this->clientBuilder
            ->name('bbbbbb')->lname($value. 'zzzz')
            ->create();

        $twilio = $this->twilioSmsBuilder->create();
        $activity = $this->activityBuilder->create();

        $rec_1 = $this->communicationRecordBuilder
            ->division($division)
            ->sort_at($date->subMinutes(10))
            ->entity($activity)
            ->client($client_1)
            ->create();

        $rec_2 = $this->communicationRecordBuilder
            ->division($division)
            ->sort_at($date->subMinutes(11))
            ->entity($activity)
            ->client($client_2)
            ->create();

        $rec_3 = $this->communicationRecordBuilder
            ->division($division)
            ->sort_at($date->subMinutes(12))
            ->entity($twilio)
            ->client($client_3)
            ->create();

        $data = $this->data;
        $data['filters']['searchTerm'] = $value;

        $this->post(route('communications.v2.records'), $data)
            ->assertJson([
                'records' => [
                    ['id' => $rec_1->id],
                    ['id' => $rec_3->id],
                ]
            ])
            ->assertJsonCount(2, 'records')
        ;
    }

    /** @test */
    public function search_client_by_phone()
    {
        $this->loginUser();

        $date = CarbonImmutable::now();

        $value = '+1(111)11111';

        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $this->session(['division' => $division->toArray()]);

        /** @var $client_1 Client */
        $client_1 = $this->clientBuilder
            ->name('aaaaaa')->lname('bbbbbbb')
            ->create();

        $client_2 = $this->clientBuilder
            ->name('zzzzz')->lname('zzzzzz')
            ->create();

        $client_3 = $this->clientBuilder
            ->name('bbbbbb')->lname('zzzz')
            ->create();
        $this->phoneBuilder->value('11111111111444')->client($client_3)->create();

        $twilio = $this->twilioSmsBuilder->create();
        $activity = $this->activityBuilder->create();

        $rec_1 = $this->communicationRecordBuilder
            ->division($division)
            ->sort_at($date->subMinutes(10))
            ->entity($activity)
            ->client($client_1)
            ->create();

        $rec_2 = $this->communicationRecordBuilder
            ->division($division)
            ->sort_at($date->subMinutes(11))
            ->entity($activity)
            ->client($client_2)
            ->create();

        $rec_3 = $this->communicationRecordBuilder
            ->division($division)
            ->sort_at($date->subMinutes(12))
            ->entity($twilio)
            ->client($client_3)
            ->create();

        $data = $this->data;
        $data['filters']['searchTerm'] = $value;

        $this->post(route('communications.v2.records'), $data)
            ->assertJson([
                'records' => [
                    ['id' => $rec_3->id],
                ]
            ])
            ->assertJsonCount(1, 'records')
        ;
    }

    /** @test */
    public function search_client_by_phone_and_rec_order_id()
    {
        $this->loginUser();

        $date = CarbonImmutable::now();

        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $this->session(['division' => $division->toArray()]);

        /** @var $client_1 Client */
        $client_1 = $this->clientBuilder
            ->name('aaaaaa')->lname('bbbbbbb')
            ->create();

        /** @var $order Order */
        $order = $this->orderBuilder
            ->client($client_1)
            ->create();

        $client_2 = $this->clientBuilder
            ->name('zzzzz')->lname('zzzzzz')
            ->create();

        $client_3 = $this->clientBuilder
            ->name('bbbbbb')->lname('zzzz')
            ->create();
        $this->phoneBuilder->value($order->id. '11111111111444')
            ->client($client_3)->create();

        $twilio = $this->twilioSmsBuilder->create();
        $activity = $this->activityBuilder->create();

        $rec_1 = $this->communicationRecordBuilder
            ->division($division)
            ->sort_at($date->subMinutes(10))
            ->entity($activity)
            ->client($client_1)
            ->order($order)
            ->create();

        $rec_2 = $this->communicationRecordBuilder
            ->division($division)
            ->sort_at($date->subMinutes(11))
            ->entity($activity)
            ->client($client_2)
            ->create();

        $rec_3 = $this->communicationRecordBuilder
            ->division($division)
            ->sort_at($date->subMinutes(12))
            ->entity($twilio)
            ->client($client_3)
            ->create();

        $data = $this->data;
        $data['filters']['searchTerm'] = (string)$order->id;

        $this->post(route('communications.v2.records'), $data)
            ->assertJson([
                'records' => [
                    ['id' => $rec_1->id],
                    ['id' => $rec_3->id],
                ]
            ])
            ->assertJsonCount(2, 'records')
        ;
    }

    /** @test */
    public function search_client_by_order_id()
    {
        $this->loginUser();

        $date = CarbonImmutable::now();

        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $this->session(['division' => $division->toArray()]);

        /** @var $client_1 Client */
        $client_1 = $this->clientBuilder
            ->name('aaaaaa')->lname('bbbbbbb')
            ->create();

        /** @var $order Order */
        $order = $this->orderBuilder
            ->client($client_1)
            ->create();

        $client_3 = $this->clientBuilder
            ->name('bbbbbb')->lname('zzzz')
            ->create();
        $this->phoneBuilder->value('11111111111444')
            ->client($client_3)->create();

        $twilio = $this->twilioSmsBuilder->create();
        $activity = $this->activityBuilder->create();

        $rec_1 = $this->communicationRecordBuilder
            ->division($division)
            ->sort_at($date->subMinutes(10))
            ->entity($activity)
            ->client($client_1)
            ->create();

        $rec_2 = $this->communicationRecordBuilder
            ->division($division)
            ->sort_at($date->subMinutes(11))
            ->entity($twilio)
            ->client($client_1)
            ->create();

        $rec_3 = $this->communicationRecordBuilder
            ->division($division)
            ->sort_at($date->subMinutes(12))
            ->entity($twilio)
            ->client($client_3)
            ->create();

        $data = $this->data;
        $data['filters']['searchTerm'] = (string)$order->id;

        $this->post(route('communications.v2.records'), $data)
            ->assertJson([
                'records' => [
                    ['id' => $rec_1->id],
                ]
            ])
            ->assertJsonCount(1, 'records')
        ;
    }

    /** @test */
    public function search_not_found_by_client_name()
    {
        $this->loginUser();

        $date = CarbonImmutable::now();

        $value = 'test';

        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $this->session(['division' => $division->toArray()]);

        /** @var $client_1 Client */
        $client_1 = $this->clientBuilder
            ->name('aaaaaa')->lname('bbbbbbb')
            ->create();

        $client_2 = $this->clientBuilder
            ->name('zzzzz')->lname('zzzzzz')
            ->create();

        $client_3 = $this->clientBuilder
            ->name('bbbbbb')->lname('zzzz')
            ->create();

        $twilio = $this->twilioSmsBuilder->create();
        $activity = $this->activityBuilder->create();

        $rec_1 = $this->communicationRecordBuilder
            ->division($division)
            ->sort_at($date->subMinutes(10))
            ->entity($activity)
            ->client($client_1)
            ->create();

        $rec_2 = $this->communicationRecordBuilder
            ->division($division)
            ->sort_at($date->subMinutes(11))
            ->entity($activity)
            ->client($client_2)
            ->create();

        $rec_3 = $this->communicationRecordBuilder
            ->division($division)
            ->sort_at($date->subMinutes(12))
            ->entity($twilio)
            ->client($client_3)
            ->create();

        $data = $this->data;
        $data['filters']['searchTerm'] = $value;

        $this->post(route('communications.v2.records'), $data)
            ->assertJson([
                'records' => []
            ])
            ->assertJsonCount(0, 'records')
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

        $this->post(route('communications.v2.records'), $this->data)
            ->assertJson([
                'success' => true,
                'more' => false,
                'page' => 1,
                'timezone' => $division->miscs['tz'],
                'records' => []
            ])
            ->assertJsonCount(0, 'records')
        ;
    }
}
