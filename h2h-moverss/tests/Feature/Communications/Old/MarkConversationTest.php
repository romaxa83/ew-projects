<?php

namespace Tests\Feature\Communications\Old;

use App\Enums\Communications\ConversationContactType;
use App\Models\Client;
use App\Models\Communications\CommunicationRecord;
use App\Models\Communications\ConversationMark;
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

class MarkConversationTest extends TestCase
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
            'type' => ConversationMark::TYPE_READ
        ];

        parent::setUp();
    }

    /** @test */
    public function success_with_client()
    {
        $user = $this->loginUser();

        /** @var $client Client */
        $client = $this->clientBuilder->create();

        /** @var $division Division */
        $division = $this->divisionBuilder->create();

        /** @var $twilio TwilioSms */
        $twilio = $this->twilioSmsBuilder->create();

        $this->session(['division' => $division->toArray()]);

        $rec = $this->communicationRecordBuilder
            ->entity($twilio)
            ->division($division)
            ->is_answered(false)
            ->create();

        $this->assertNull(ConversationMark::first());
        $this->assertNull(
            CommunicationRecord::query()
                ->where('entity_type', ConversationMark::MORPH_NAME)
                ->first()
        );

        $data = $this->data;
        $data['conversation'] = [
            'channelContact' => $twilio->from,
            'client' => $client->toArray(),
            'isAnswered' => false,
            'type' => FormatterService::getType($twilio),
            'uid' => FormatterService::getUid($twilio),
            'id' => $rec->id,
        ];

        $this->post(route('communications.markConversation'), $data)
            ->assertJson([
                'success' => true
            ])
        ;

        $conversation = ConversationMark::first();

        $this->assertEquals($conversation->type, ConversationMark::TYPE_READ);
        $this->assertEquals($conversation->client_id, $client->id);
        $this->assertEquals($conversation->user_id, $user->id);
        $this->assertNull($conversation->contact_type);
        $this->assertNull($conversation->contact_value);

        $recMark = CommunicationRecord::query()
            ->where('entity_type', ConversationMark::MORPH_NAME)
            ->first();

        $this->assertEquals($recMark->entity_id, $conversation->id);
        $this->assertEquals($recMark->client_id, $client->id);
        $this->assertNull($recMark->client_ids);
        $this->assertEquals($recMark->division_id, $division->id);
        $this->assertTrue($recMark->is_answered);
        $this->assertNull($recMark->channel_contact);

        $rec->refresh();

        $this->assertTrue($rec->is_answered);
    }

    /** @test */
    public function success_without_client_but_has_channel_contact_as_phone_by_twilio()
    {
        $user = $this->loginUser();

        $value = '56576567576';

        /** @var $client Client */
        $client = $this->clientBuilder->create();
        $this->phoneBuilder
            ->client($client)
            ->value($value)
            ->create();

        /** @var $division Division */
        $division = $this->divisionBuilder->create();

        /** @var $twilio TwilioSms */
        $twilio = $this->twilioSmsBuilder->create();

        $this->session(['division' => $division->toArray()]);

        $this->assertNull(ConversationMark::first());
        $this->assertNull(
            CommunicationRecord::query()
                ->where('entity_type', ConversationMark::MORPH_NAME)
                ->first()
        );

        $rec = $this->communicationRecordBuilder
            ->entity($twilio)
            ->division($division)
            ->is_answered(false)
            ->create();

        $data = $this->data;
        $data['conversation'] = [
            'channelContact' => $value,
            'client' => null,
            'isAnswered' => false,
            'type' => FormatterService::getType($twilio),
            'uid' => FormatterService::getUid($twilio),
        ];

        $this->post(route('communications.markConversation'), $data)
            ->assertJson([
                'success' => true
            ])
        ;

        $conversation = ConversationMark::first();

        $this->assertEquals($conversation->type, ConversationMark::TYPE_READ);
        $this->assertNull($conversation->client_id);
        $this->assertEquals($conversation->user_id, $user->id);
        $this->assertEquals($conversation->contact_type, ConversationContactType::Phone->value);
        $this->assertEquals($conversation->contact_value, $value);

        $recMark = CommunicationRecord::query()
            ->where('entity_type', ConversationMark::MORPH_NAME)
            ->first();

        $this->assertEquals($recMark->entity_id, $conversation->id);
        $this->assertEquals($recMark->client_id, $client->id);

        $rec->refresh();

        $this->assertTrue($rec->is_answered);
    }

    /** @test */
    public function success_without_client_but_has_channel_contact_as_phone_by_ringostat()
    {
        $user = $this->loginUser();

        $value = '56576567576';

        /** @var $client Client */
        $client = $this->clientBuilder->create();
        $this->phoneBuilder
            ->client($client)
            ->value($value)
            ->create();

        /** @var $division Division */
        $division = $this->divisionBuilder->create();

        /** @var $ringostat EventAfterCall */
        $ringostat = $this->ringostatBuilder->create();

        $this->session(['division' => $division->toArray()]);

        $rec = $this->communicationRecordBuilder
            ->entity($ringostat)
            ->division($division)
            ->is_answered(false)
            ->create();

        $this->assertNull(ConversationMark::first());
        $this->assertNull(
            CommunicationRecord::query()
                ->where('entity_type', ConversationMark::MORPH_NAME)
                ->first()
        );

        $data = $this->data;
        $data['conversation'] = [
            'channelContact' => $value,
            'client' => null,
            'isAnswered' => false,
            'type' => FormatterService::getType($ringostat),
            'uid' => FormatterService::getUid($ringostat),
        ];

        $this->post(route('communications.markConversation'), $data)
            ->assertJson([
                'success' => true
            ])
        ;

        $conversation = ConversationMark::first();

        $this->assertEquals($conversation->type, ConversationMark::TYPE_READ);
        $this->assertNull($conversation->client_id);
        $this->assertEquals($conversation->user_id, $user->id);
        $this->assertEquals($conversation->contact_type, ConversationContactType::Phone->value);
        $this->assertEquals($conversation->contact_value, $value);

        $recMark = CommunicationRecord::query()
            ->where('entity_type', ConversationMark::MORPH_NAME)
            ->first();

        $this->assertEquals($recMark->entity_id, $conversation->id);
        $this->assertEquals($recMark->client_id, $client->id);

        $rec->refresh();

        $this->assertTrue($rec->is_answered);
    }

    /** @test */
    public function success_without_client_but_has_channel_contact_as_phone_by_zadarma_call()
    {
        $user = $this->loginUser();

        $value = '56576567576';

        /** @var $client Client */
        $client = $this->clientBuilder->create();
        $this->phoneBuilder
            ->client($client)
            ->value($value)
            ->create();

        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $this->session(['division' => $division->toArray()]);

        /** @var $zadarama CallsEvents */
        $zadarama = $this->zadarmaCallBuilder->create();

        $rec = $this->communicationRecordBuilder
            ->entity($zadarama)
            ->division($division)
            ->is_answered(false)
            ->create();

        $this->assertNull(ConversationMark::first());
        $this->assertNull(
            CommunicationRecord::query()
                ->where('entity_type', ConversationMark::MORPH_NAME)
                ->first()
        );

        $data = $this->data;
        $data['conversation'] = [
            'channelContact' => $value,
            'client' => null,
            'isAnswered' => false,
            'type' => FormatterService::getType($zadarama),
            'uid' => FormatterService::getUid($zadarama),
        ];

        $this->post(route('communications.markConversation'), $data)
            ->assertJson([
                'success' => true
            ])
        ;

        $conversation = ConversationMark::first();

        $this->assertEquals($conversation->type, ConversationMark::TYPE_READ);
        $this->assertNull($conversation->client_id);
        $this->assertEquals($conversation->user_id, $user->id);
        $this->assertEquals($conversation->contact_type, ConversationContactType::Phone->value);
        $this->assertEquals($conversation->contact_value, $value);

        $recMark = CommunicationRecord::query()
            ->where('entity_type', ConversationMark::MORPH_NAME)
            ->first();

        $this->assertEquals($recMark->entity_id, $conversation->id);
        $this->assertEquals($recMark->client_id, $client->id);

        $rec->refresh();

        $this->assertTrue($rec->is_answered);
    }

    /** @test */
    public function success_without_client_but_has_channel_contact_as_phone_by_zadarma_sms()
    {
        $user = $this->loginUser();

        $value = '56576567576';

        /** @var $client Client */
        $client = $this->clientBuilder->create();
        $this->phoneBuilder
            ->client($client)
            ->value($value)
            ->create();

        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $this->session(['division' => $division->toArray()]);

        /** @var $zadarama SmsEvents */
        $zadarama = $this->zadarmaSmsBuilder->create();

        $rec = $this->communicationRecordBuilder
            ->entity($zadarama)
            ->division($division)
            ->is_answered(false)
            ->create();

        $this->assertNull(ConversationMark::first());
        $this->assertNull(
            CommunicationRecord::query()
                ->where('entity_type', ConversationMark::MORPH_NAME)
                ->first()
        );

        $data = $this->data;
        $data['conversation'] = [
            'channelContact' => $value,
            'client' => null,
            'isAnswered' => false,
            'type' => FormatterService::getType($zadarama),
            'uid' => FormatterService::getUid($zadarama),
        ];

        $this->post(route('communications.markConversation'), $data)
            ->assertJson([
                'success' => true
            ])
        ;

        $conversation = ConversationMark::first();

        $this->assertEquals($conversation->type, ConversationMark::TYPE_READ);
        $this->assertNull($conversation->client_id);
        $this->assertEquals($conversation->user_id, $user->id);
        $this->assertEquals($conversation->contact_type, ConversationContactType::Phone->value);
        $this->assertEquals($conversation->contact_value, $value);

        $recMark = CommunicationRecord::query()
            ->where('entity_type', ConversationMark::MORPH_NAME)
            ->first();

        $this->assertEquals($recMark->entity_id, $conversation->id);
        $this->assertEquals($recMark->client_id, $client->id);

        $rec->refresh();

        $this->assertTrue($rec->is_answered);
    }

    /** @test */
    public function success_without_client_but_has_channel_contact_as_email_by_gmail()
    {
        $user = $this->loginUser();

        $value = 'test@gmail.com';

        /** @var $client Client */
        $client = $this->clientBuilder->create();
        $this->emailBuilder
            ->client($client)
            ->value($value)
            ->create();

        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $this->session(['division' => $division->toArray()]);

        /** @var $message Message */
        $message = $this->messageBuilder->create();

        $rec = $this->communicationRecordBuilder
            ->entity($message)
            ->division($division)
            ->is_answered(false)
            ->create();

        $this->assertNull(ConversationMark::first());
        $this->assertNull(
            CommunicationRecord::query()
                ->where('entity_type', ConversationMark::MORPH_NAME)
                ->first()
        );

        $data = $this->data;
        $data['conversation'] = [
            'channelContact' => $value,
            'client' => null,
            'isAnswered' => false,
            'type' => FormatterService::getType($message),
            'uid' => FormatterService::getUid($message),
        ];

        $this->post(route('communications.markConversation'), $data)
            ->assertJson([
                'success' => true
            ])
        ;

        $conversation = ConversationMark::first();

        $this->assertEquals($conversation->type, ConversationMark::TYPE_READ);
        $this->assertNull($conversation->client_id);
        $this->assertEquals($conversation->user_id, $user->id);
        $this->assertEquals($conversation->contact_type, ConversationContactType::Email->value);
        $this->assertEquals($conversation->contact_value, $value);

        $recMark = CommunicationRecord::query()
            ->where('entity_type', ConversationMark::MORPH_NAME)
            ->first();

        $this->assertEquals($recMark->entity_id, $conversation->id);
        $this->assertEquals($recMark->client_id, $client->id);

        $rec->refresh();

        $this->assertTrue($rec->is_answered);
    }

    /** @test */
    public function success_without_client_and_channel_contact()
    {
        $this->loginUser();

        $value = 'test@gmail.com';

        /** @var $client Client */
        $client = $this->clientBuilder->create();
        $this->emailBuilder
            ->client($client)
            ->value($value)
            ->create();

        /** @var $division Division */
        $division = $this->divisionBuilder->create();

        /** @var $message Message */
        $message = $this->messageBuilder->create();

        $this->session(['division' => $division->toArray()]);

        $this->assertNull(ConversationMark::first());

        $data = $this->data;
        $data['conversation'] = [
            'channelContact' => null,
            'client' => null,
            'isAnswered' => false,
            'type' => FormatterService::getType($message),
            'uid' => FormatterService::getUid($message),
        ];

        $this->post(route('communications.markConversation'), $data)
            ->assertJson([
                'success' => false,
                "msg" => "Conversation mark without valid contact!"
            ])
        ;

        $this->assertNull(ConversationMark::first());
    }

    /** @test */
    public function success_without_client_and_channel_contact_by_type()
    {
        $this->loginUser();

        $value = 'test@gmail.com';

        /** @var $client Client */
        $client = $this->clientBuilder->create();
        $this->emailBuilder
            ->client($client)
            ->value($value)
            ->create();

        /** @var $division Division */
        $division = $this->divisionBuilder->create();

        /** @var $activity Client\Activity */
        $activity = $this->activityBuilder->create();

        $this->session(['division' => $division->toArray()]);

        $this->assertNull(ConversationMark::first());

        $data = $this->data;
        $data['conversation'] = [
            'channelContact' => $value,
            'client' => null,
            'isAnswered' => false,
            'type' => FormatterService::getType($activity),
            'uid' => FormatterService::getUid($activity),
        ];

        $this->post(route('communications.markConversation'), $data)
            ->assertJson([
                'success' => false,
                "msg" => "Conversation mark without valid contact!"
            ])
        ;

        $this->assertNull(ConversationMark::first());
    }
}
