<?php

namespace Tests\Unit\Services\Communications\IncomingCallService;

use App\Enums\ProviderEnum;
use App\Models\Calls\IncomingCall;
use App\Models\Client;
use App\Models\Ringostat\EventBeforeCall;
use App\Models\Zadarma\CallsEvents;
use App\Services\Communications\IncomingCallService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Clients\ClientBuilder;
use Tests\Builders\Clients\PhoneBuilder;
use Tests\Builders\Ringostat\EventBeforeCallBuilder;
use Tests\Builders\Zadarma\CallEventBuilder;
use Tests\TestCase;

class CreateTest extends TestCase
{
    use DatabaseTransactions;

    protected EventBeforeCallBuilder $eventBeforeCallBuilder;
    protected CallEventBuilder $callBuilder;
    protected ClientBuilder $clientBuilder;
    protected PhoneBuilder $clientPhoneBuilder;

    public function setUp(): void
    {
        $this->eventBeforeCallBuilder = resolve(EventBeforeCallBuilder::class);
        $this->callBuilder = resolve(CallEventBuilder::class);
        $this->clientBuilder = resolve(ClientBuilder::class);
        $this->clientPhoneBuilder = resolve(PhoneBuilder::class);

        parent::setUp();
    }

    /** @test */
    public function create()
    {
        /** @var $event CallsEvents */
        $event = $this->callBuilder->create();

        $this->assertEquals(0, IncomingCall::count());

        IncomingCallService::handler($event);

        /** @var $call IncomingCall */
        $call = IncomingCall::first();

        $this->assertEquals($call->provider->value, ProviderEnum::Zadarma());
        $this->assertEquals($call->call_id, $event->pbx_call_id);
        $this->assertEquals($call->phone, $event->destination);
        $this->assertNull($call->client_id);
    }

    /** @test */
    public function create_with_client()
    {
        /** @var $client Client */
        $client = $this->clientBuilder->create();
        /** @var $phone Client\Phone */
        $phone = $this->clientPhoneBuilder->client($client)->create();

        /** @var $event CallsEvents */
        $event = $this->callBuilder->destination($phone->value)->create();

        $this->assertEquals(0, IncomingCall::count());

        IncomingCallService::handler($event);

        /** @var $call IncomingCall */
        $call = IncomingCall::first();

        $this->assertEquals($call->client_id, $client->id);
    }

    /** @test */
    public function create_as_ringostat()
    {
        /** @var $event EventBeforeCall */
        $event = $this->eventBeforeCallBuilder->create();

        $this->assertEquals(0, IncomingCall::count());

        IncomingCallService::handler($event);

        /** @var $call IncomingCall */
        $call = IncomingCall::first();

        $this->assertEquals($call->provider->value, ProviderEnum::Ringostat());
        $this->assertEquals($call->call_id, $event->call_id);
        $this->assertEquals($call->phone, $event->callers_number);
        $this->assertNull($call->client_id);
    }

    /** @test */
    public function create_as_ringostat_with_client()
    {
        /** @var $client Client */
        $client = $this->clientBuilder->create();
        /** @var $event EventBeforeCall */
        $event = $this->eventBeforeCallBuilder->client($client)->create();

        $this->assertEquals(0, IncomingCall::count());

        IncomingCallService::handler($event);

        /** @var $call IncomingCall */
        $call = IncomingCall::first();

        $this->assertEquals($call->client_id, $client->id);
    }
}

