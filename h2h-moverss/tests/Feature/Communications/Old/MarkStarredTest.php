<?php

namespace Tests\Feature\Communications\Old;

use App\Models\Client;
use App\Models\Communications\ConversationFavorites;
use App\Models\Division;
use App\Models\Mailbox\Gmail\Message;
use App\Models\Ringostat\EventAfterCall;
use App\Models\Twilio\TwilioSms;
use App\Models\Zadarma\CallsEvents;
use App\Models\Zadarma\SmsEvents;
use App\Services\Communications\FormatterService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Clients\ActivityBuilder;
use Tests\Builders\Clients\ClientBuilder;
use Tests\Builders\Clients\EmailBuilder;
use Tests\Builders\Clients\PhoneBuilder;
use Tests\Builders\Communications\CommunicationRecordBuilder;
use Tests\Builders\Divisions\DivisionBuilder;
use Tests\Builders\Gmail\MessageBuilder;
use Tests\Builders\Ringostat\EventAfterCallBuilder;
use Tests\Builders\Twilio\TwilioSmsBuilder;
use Tests\Builders\Users\UserBuilder;
use Tests\Builders\Zadarma\CallEventBuilder;
use Tests\Builders\Zadarma\SmsEventBuilder;
use Tests\TestCase;

class MarkStarredTest extends TestCase
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

    protected CommunicationRecordBuilder $communicationRecordBuilder;
    protected PhoneBuilder $phoneBuilder;
    protected EmailBuilder $emailBuilder;

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
        $this->communicationRecordBuilder = resolve(CommunicationRecordBuilder::class);
        $this->phoneBuilder = resolve(PhoneBuilder::class);
        $this->emailBuilder = resolve(EmailBuilder::class);

        $this->data = [
            'conversation' => [
                'channelContact' => null,
                'client' => null,
                'uid' => null
            ],
            'starred' => true
        ];

        parent::setUp();
    }

    /** @test */
    public function success_with_communication_rec_id()
    {
        $user = $this->loginUser();

        /** @var $client Client */
        $client = $this->clientBuilder->create();

        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $this->session(['division' => $division->toArray()]);

        /** @var $twilio TwilioSms */
        $twilio = $this->twilioSmsBuilder->create();

        $rec = $this->communicationRecordBuilder
            ->entity($twilio)
            ->division($division)
            ->create();

        $this->assertNull(ConversationFavorites::first());

        $data = $this->data;
        $data['conversation'] = [
            'channelContact' => $twilio->from,
            'client' => $client->toArray(),
            'isAnswered' => false,
            'type' => FormatterService::getType($twilio),
            'uid' => FormatterService::getUid($twilio),
            'id' => $rec->id,
        ];

        $this->post(route('communications.markStarred'), $data)
            ->assertJson([
                'success' => true
            ])
        ;

        $conversation = ConversationFavorites::first();

        $this->assertTrue($conversation->starred);
        $this->assertEquals($conversation->client_id, $client->id);
        $this->assertEquals($conversation->user_id, $user->id);
        $this->assertEquals($conversation->communication_rec_id, $rec->id);
    }

    /** @test */
    public function success_without_communication_rec_id_as_twilio()
    {
        $user = $this->loginUser();

        /** @var $client Client */
        $client = $this->clientBuilder->create();

        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $this->session(['division' => $division->toArray()]);

        /** @var $twilio TwilioSms */
        $twilio = $this->twilioSmsBuilder->create();

        $rec = $this->communicationRecordBuilder
            ->entity($twilio)
            ->division($division)
            ->create();

        $this->assertNull(ConversationFavorites::first());

        $data = $this->data;
        $data['conversation'] = [
            'channelContact' => $twilio->from,
            'client' => $client->toArray(),
            'isAnswered' => false,
            'type' => FormatterService::getType($twilio),
            'uid' => FormatterService::getUid($twilio),
        ];

        $this->post(route('communications.markStarred'), $data)
            ->assertJson([
                'success' => true
            ])
        ;

        $conversation = ConversationFavorites::first();

        $this->assertTrue($conversation->starred);
        $this->assertEquals($conversation->client_id, $client->id);
        $this->assertEquals($conversation->user_id, $user->id);
        $this->assertEquals($conversation->communication_rec_id, $rec->id);
    }

    /** @test */
    public function success_without_communication_rec_id_as_ringostat()
    {
        $user = $this->loginUser();

        /** @var $client Client */
        $client = $this->clientBuilder->create();

        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $this->session(['division' => $division->toArray()]);

        /** @var $entity EventAfterCall */
        $entity = $this->ringostatBuilder->create();

        $rec = $this->communicationRecordBuilder
            ->entity($entity)
            ->division($division)
            ->create();

        $this->assertNull(ConversationFavorites::first());

        $data = $this->data;
        $data['conversation'] = [
            'channelContact' => $entity->id,
            'client' => $client->toArray(),
            'isAnswered' => false,
            'type' => FormatterService::getType($entity),
            'uid' => FormatterService::getUid($entity),
        ];

        $this->post(route('communications.markStarred'), $data)
            ->assertJson([
                'success' => true
            ])
        ;

        $conversation = ConversationFavorites::first();

        $this->assertTrue($conversation->starred);
        $this->assertEquals($conversation->client_id, $client->id);
        $this->assertEquals($conversation->user_id, $user->id);
        $this->assertEquals($conversation->communication_rec_id, $rec->id);
    }

    /** @test */
    public function success_without_communication_rec_id_as_message()
    {
        $user = $this->loginUser();

        /** @var $client Client */
        $client = $this->clientBuilder->create();

        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $this->session(['division' => $division->toArray()]);

        /** @var $entity Message */
        $entity = $this->messageBuilder->create();
        $entity->communicationRecord()->delete();

        $rec = $this->communicationRecordBuilder
            ->entity($entity)
            ->division($division)
            ->create();

        $this->assertNull(ConversationFavorites::first());

        $data = $this->data;
        $data['conversation'] = [
            'channelContact' => $entity->id,
            'client' => $client->toArray(),
            'isAnswered' => false,
            'type' => FormatterService::getType($entity),
            'uid' => FormatterService::getUid($entity),
        ];

        $this->post(route('communications.markStarred'), $data)
            ->assertJson([
                'success' => true
            ])
        ;

        $conversation = ConversationFavorites::first();

        $this->assertTrue($conversation->starred);
        $this->assertEquals($conversation->client_id, $client->id);
        $this->assertEquals($conversation->user_id, $user->id);
        $this->assertEquals($conversation->communication_rec_id, $rec->id);
    }

    /** @test */
    public function success_without_communication_rec_id_as_zadarma_call()
    {
        $user = $this->loginUser();

        /** @var $client Client */
        $client = $this->clientBuilder->create();

        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $this->session(['division' => $division->toArray()]);

        /** @var $entity CallsEvents */
        $entity = $this->zadarmaCallBuilder->create();

        $rec = $this->communicationRecordBuilder
            ->entity($entity)
            ->division($division)
            ->create();

        $this->assertNull(ConversationFavorites::first());

        $data = $this->data;
        $data['conversation'] = [
            'channelContact' => $entity->id,
            'client' => $client->toArray(),
            'isAnswered' => false,
            'type' => FormatterService::getType($entity),
            'uid' => FormatterService::getUid($entity),
        ];

        $this->post(route('communications.markStarred'), $data)
            ->assertJson([
                'success' => true
            ])
        ;

        $conversation = ConversationFavorites::first();

        $this->assertTrue($conversation->starred);
        $this->assertEquals($conversation->client_id, $client->id);
        $this->assertEquals($conversation->user_id, $user->id);
        $this->assertEquals($conversation->communication_rec_id, $rec->id);
    }

    /** @test */
    public function success_without_communication_rec_id_as_zadarma_sms()
    {
        $user = $this->loginUser();

        /** @var $client Client */
        $client = $this->clientBuilder->create();

        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $this->session(['division' => $division->toArray()]);

        /** @var $entity SmsEvents */
        $entity = $this->zadarmaSmsBuilder->create();

        $rec = $this->communicationRecordBuilder
            ->entity($entity)
            ->division($division)
            ->create();

        $this->assertNull(ConversationFavorites::first());

        $data = $this->data;
        $data['conversation'] = [
            'channelContact' => $entity->id,
            'client' => $client->toArray(),
            'isAnswered' => false,
            'type' => FormatterService::getType($entity),
            'uid' => FormatterService::getUid($entity),
        ];

        $this->post(route('communications.markStarred'), $data)
            ->assertJson([
                'success' => true
            ])
        ;

        $conversation = ConversationFavorites::first();

        $this->assertTrue($conversation->starred);
        $this->assertEquals($conversation->client_id, $client->id);
        $this->assertEquals($conversation->user_id, $user->id);
        $this->assertEquals($conversation->communication_rec_id, $rec->id);
    }
}
