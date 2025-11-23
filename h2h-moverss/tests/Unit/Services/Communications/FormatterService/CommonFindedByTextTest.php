<?php

namespace Tests\Unit\Services\Communications\FormatterService;

use App\Models\Client;
use App\Models\Communications\CommunicationRecord;
use App\Models\Division;
use App\Models\Ringostat\EventAfterCall;
use App\Models\Twilio\TwilioSms;
use App\Services\Communications\FormatterService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Clients\ClientBuilder;
use Tests\Builders\Clients\EmailBuilder;
use Tests\Builders\Clients\PhoneBuilder;
use Tests\Builders\Communications\CommunicationRecordBuilder;
use Tests\Builders\Divisions\DivisionBuilder;
use Tests\Builders\Orders\OrderBuilder;
use Tests\Builders\Ringostat\EventAfterCallBuilder;
use Tests\Builders\Twilio\TwilioSmsBuilder;
use Tests\Builders\Zadarma\CallEventBuilder;
use Tests\TestCase;

class CommonFindedByTextTest extends TestCase
{
    use DatabaseTransactions;

    protected ClientBuilder $clientBuilder;
    protected PhoneBuilder $phoneBuilder;
    protected EmailBuilder $emailBuilder;
    protected TwilioSmsBuilder $twilioSmsBuilder;
    protected EventAfterCallBuilder $eventAfterCallBuilder;
    protected CallEventBuilder $callBuilder;
    protected OrderBuilder $orderBuilder;

    protected DivisionBuilder $divisionBuilder;
    protected CommunicationRecordBuilder $communicationRecordBuilder;
    protected FormatterService $service;

    public function setUp(): void
    {
        $this->clientBuilder = resolve(ClientBuilder::class);
        $this->phoneBuilder = resolve(PhoneBuilder::class);
        $this->emailBuilder = resolve(EmailBuilder::class);
        $this->twilioSmsBuilder = resolve(TwilioSmsBuilder::class);
        $this->callBuilder = resolve(CallEventBuilder::class);
        $this->divisionBuilder = resolve(DivisionBuilder::class);
        $this->communicationRecordBuilder = resolve(CommunicationRecordBuilder::class);
        $this->eventAfterCallBuilder = resolve(EventAfterCallBuilder::class);
        $this->orderBuilder = resolve(OrderBuilder::class);
        $this->service = resolve(FormatterService::class);


        parent::setUp();
    }

    /** @test */
    public function by_client_name()
    {
        $search = 'wer';

        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        /** @var $client Client */
        $client = $this->clientBuilder
            ->name('aa'. $search)
            ->lname('tost')
            ->create();

        /** @var $entity TwilioSms */
        $entity = $this->twilioSmsBuilder
            ->direction(TwilioSms::INBOUND_VALUE)
            ->create();
        /** @var $model CommunicationRecord */
        $model = $this->communicationRecordBuilder
            ->entity($entity)
            ->client($client)
            ->division($division)
            ->channel_contact($client->id)
            ->create();

        $rec = $this->service->recForMainPanel($model, searchTerm: $search);

        $this->assertEquals(
            $rec['findedByText'],
            "Name: aa<mark>{$search}</mark> tost."
        );
    }

    /** @test */
    public function by_client_phone()
    {
        $search = '111';

        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        /** @var $client Client */
        $client = $this->clientBuilder
            ->name('aa')
            ->lname('tost')
            ->create();

        $this->phoneBuilder->client($client)->value($search. '777')->create();
        $this->phoneBuilder->client($client)->value('7878787878')->create();

        /** @var $entity  */
        $entity = $this->callBuilder
            ->create();
        /** @var $model CommunicationRecord */
        $model = $this->communicationRecordBuilder
            ->entity($entity)
            ->client($client)
            ->division($division)
            ->channel_contact($client->id)
            ->create();

        $rec = $this->service->recForMainPanel($model, searchTerm: $search);

        $this->assertEquals(
            $rec['findedByText'],
            "Phone: +1<mark>{$search}</mark>777."
        );
    }

    /** @test */
    public function by_client_phone_and_email()
    {
        $search = '111';

        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        /** @var $client Client */
        $client = $this->clientBuilder
            ->name('aa')
            ->lname('tost')
            ->create();

        $this->phoneBuilder->client($client)->value($search. '777')->create();
        $this->emailBuilder->client($client)->value('test.'.$search.'@gmail.com')->create();

        /** @var $entity TwilioSms */
        $entity = $this->twilioSmsBuilder
            ->direction(TwilioSms::INBOUND_VALUE)
            ->create();
        /** @var $model CommunicationRecord */
        $model = $this->communicationRecordBuilder
            ->entity($entity)
            ->client($client)
            ->division($division)
            ->channel_contact($client->id)
            ->create();

        $rec = $this->service->recForMainPanel($model, searchTerm: $search);

        $this->assertEquals(
            $rec['findedByText'],
            "Phone: +1<mark>{$search}</mark>777. Email: test.<mark>{$search}</mark>@gmail.com."
        );
    }

    /** @test */
    public function by_contact()
    {
        $search = '23332';

        /** @var $division Division */
        $division = $this->divisionBuilder->create();

        /** @var $entity TwilioSms */
        $entity = $this->twilioSmsBuilder
            ->direction(TwilioSms::INBOUND_VALUE)
            ->create();
        /** @var $model CommunicationRecord */
        $model = $this->communicationRecordBuilder
            ->entity($entity)
            ->client(null)
            ->division($division)
            ->channel_contact($search . '908')
            ->create();

        $rec = $this->service->recForMainPanel($model, searchTerm: $search);

        $this->assertEquals(
            $rec['findedByText'],
            "Contact: <mark>{$search}</mark>908."
        );
    }

    /** @test */
    public function by_customer_id()
    {
        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        /** @var $client Client */
        $client = $this->clientBuilder
            ->name('aa')
            ->lname('tost')
            ->create();

        /** @var $entity TwilioSms */
        $entity = $this->twilioSmsBuilder
            ->direction(TwilioSms::INBOUND_VALUE)
            ->create();
        /** @var $model CommunicationRecord */
        $model = $this->communicationRecordBuilder
            ->entity($entity)
            ->client($client)
            ->division($division)
            ->channel_contact($client->id)
            ->create();

        $search = $client->id;

        $rec = $this->service->recForMainPanel($model, searchTerm: $search);

        $this->assertEquals(
            $rec['findedByText'],
            "Contact: <mark>{$search}</mark>. CustomerID: <mark>{$search}</mark>."
        );
    }

    /** @test */
    public function by_order_id()
    {
        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        /** @var $client Client */
        $client = $this->clientBuilder
            ->name('aa')
            ->lname('tost')
            ->create();

        $order = $this->orderBuilder->client($client)->create();
        $this->orderBuilder->client($client)->create();

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

        $search = $order->id;

        $rec = $this->service->recForMainPanel($model, searchTerm: $search);

        $this->assertEquals(
            $rec['findedByText'],
            "Order: #<mark>{$search}</mark>."
        );
    }
}
