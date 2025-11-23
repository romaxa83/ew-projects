<?php

namespace Tests\Unit\Services\Communications\FormatterService;

use App\Enums\Communications\ConversationContactType;
use App\Models\Attachment;
use App\Models\Client;
use App\Models\Communications\CommunicationRecord;
use App\Models\Division;
use App\Models\Twilio\TwilioSms;
use App\Services\Communications\FormatterService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Attachment\AttachmentBuilder;
use Tests\Builders\Clients\ClientBuilder;
use Tests\Builders\Clients\EmailBuilder;
use Tests\Builders\Clients\PhoneBuilder;
use Tests\Builders\Communications\CommunicationRecordBuilder;
use Tests\Builders\Communications\ConversationFavoritesBuilder;
use Tests\Builders\Divisions\DivisionBuilder;
use Tests\Builders\Twilio\TwilioSmsBuilder;
use Tests\TestCase;

class TwilioRecTest extends TestCase
{
    use DatabaseTransactions;

    protected AttachmentBuilder $attachmentBuilder;
    protected ClientBuilder $clientBuilder;
    protected PhoneBuilder $phoneBuilder;
    protected EmailBuilder $emailBuilder;
    protected TwilioSmsBuilder $twilioSmsBuilder;
    protected DivisionBuilder $divisionBuilder;
    protected CommunicationRecordBuilder $communicationRecordBuilder;
    protected ConversationFavoritesBuilder $conversationFavoritesBuilder;
    protected FormatterService $service;

    public function setUp(): void
    {
        $this->attachmentBuilder = resolve(AttachmentBuilder::class);
        $this->clientBuilder = resolve(ClientBuilder::class);
        $this->phoneBuilder = resolve(PhoneBuilder::class);
        $this->emailBuilder = resolve(EmailBuilder::class);
        $this->twilioSmsBuilder = resolve(TwilioSmsBuilder::class);
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

        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        /** @var $client Client */
        $client = $this->clientBuilder->create();

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

        $rec = $this->service->recForMainPanelBase($model);

        $this->assertEquals($rec['type'], FormatterService::getType($entity));
        $this->assertTrue($rec['item'] instanceof TwilioSms);
        $this->assertEquals($rec['item']->id, $entity->id);
        $this->assertEquals($rec['uid'], FormatterService::getUid($entity));
        $this->assertEquals($rec['id'], $model->id);
        $this->assertEquals($rec['timestamp'], $date->timestamp);

        $this->assertNull($rec['audit']);
    }

    /** @test */
    public function one_advance()
    {
        $date = CarbonImmutable::now();

        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        /** @var $client Client */
        $client = $this->clientBuilder->create();

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

        $rec = $this->service->recForMainPanel($model);

        $this->assertEquals($rec['type'], FormatterService::getType($entity));
        $this->assertTrue($rec['item'] instanceof TwilioSms);
        $this->assertEquals($rec['item']->id, $entity->id);
        $this->assertEquals($rec['uid'], FormatterService::getUid($entity));
        $this->assertEquals($rec['id'], $model->id);
        $this->assertEquals($rec['client']->id, $client->id);
        $this->assertNull($rec['collectionClients']);
        $this->assertEquals($rec['timestamp'], $date->timestamp);
        $this->assertEquals($rec['channelContact'], $client->id);
        $this->assertFalse($rec['starred']);
        $this->assertTrue($rec['isAnswered']);
        $this->assertEmpty($rec['managers']);
        $this->assertNull($rec['managerAbbr']);
        $this->assertNull($rec['managerAbbr']);
        $this->assertEmpty($rec['attachments']);

        $this->assertFalse(isset($rec['findedByText']));
    }

    /** @test */
    public function one_advance_is_starred()
    {
        $user = $this->loginUser();

        /** @var $client Client */
        $client = $this->clientBuilder->create();

        $this->conversationFavoritesBuilder
            ->client($client)
            ->user($user)
            ->starred(true)
            ->create();

        /** @var $entity TwilioSms */
        $entity = $this->twilioSmsBuilder
            ->direction(TwilioSms::INBOUND_VALUE)
            ->create();

        /** @var $model CommunicationRecord */
        $model = $this->communicationRecordBuilder
            ->entity($entity)
            ->client($client)
            ->create();

        $rec = $this->service->recForMainPanel($model);

        $this->assertTrue($rec['starred']);
    }

    /** @test */
    public function one_advance_is_starred_but_another_user()
    {
        /** @var $client Client */
        $client = $this->clientBuilder->create();

        $this->conversationFavoritesBuilder
            ->client($client)
            ->starred(true)
            ->create();

        /** @var $entity TwilioSms */
        $entity = $this->twilioSmsBuilder
            ->direction(TwilioSms::INBOUND_VALUE)
            ->create();

        /** @var $model CommunicationRecord */
        $model = $this->communicationRecordBuilder
            ->entity($entity)
            ->client($client)
            ->create();

        $rec = $this->service->recForMainPanel($model);

        $this->assertFalse($rec['starred']);
    }

    /** @test */
    public function one_advance_is_starred_but_not_have_client()
    {
        $user = $this->loginUser();

        /** @var $entity TwilioSms */
        $entity = $this->twilioSmsBuilder
            ->direction(TwilioSms::INBOUND_VALUE)
            ->create();

        /** @var $model CommunicationRecord */
        $model = $this->communicationRecordBuilder
            ->entity($entity)
            ->channel_contact($entity->from)
            ->create();

        $this->conversationFavoritesBuilder
            ->user($user)
            ->contact_type(ConversationContactType::Phone->value)
            ->contact_value($entity->from)
            ->starred(true)
            ->create();

        $rec = $this->service->recForMainPanel($model);

        $this->assertTrue($rec['starred']);
    }

    /** @test */
    public function one_advance_is_starred_but_not_have_client_and_another_user()
    {
        /** @var $entity TwilioSms */
        $entity = $this->twilioSmsBuilder
            ->direction(TwilioSms::INBOUND_VALUE)
            ->create();

        /** @var $model CommunicationRecord */
        $model = $this->communicationRecordBuilder
            ->entity($entity)
            ->channel_contact($entity->from)
            ->create();

        $this->conversationFavoritesBuilder
            ->contact_type(ConversationContactType::Phone->value)
            ->contact_value($entity->from)
            ->starred(true)
            ->create();

        $rec = $this->service->recForMainPanel($model);

        $this->assertFalse($rec['starred']);
    }

    /** @test */
    public function one_advance_with_media()
    {
        /** @var $entity TwilioSms */
        $entity = $this->twilioSmsBuilder
            ->direction(TwilioSms::INBOUND_VALUE)
            ->create();

        /** @var $model CommunicationRecord */
        $model = $this->communicationRecordBuilder
            ->entity($entity)
            ->channel_contact($entity->from)
            ->create();

        /** @var $media_1 Attachment */
        $media_1 = $this->attachmentBuilder
            ->miscs([
                'file' => [
                    'patch' => 'attachments/twilio/',
                    'size' => '28.18 kB',
                    'name' => 'img_1.png',
                    'ext' => 'png',
                ]
            ])
            ->entity($entity)->create();
        $media_2 = $this->attachmentBuilder
            ->miscs([
                'file' => [
                    'patch' => 'attachments/twilio/',
                    'size' => '28.18 kB',
                    'name' => 'img_2.png',
                    'ext' => 'png',
                ]
            ])
            ->entity($entity)->create();

        $rec = $this->service->recForMainPanel($model);

        $this->assertEquals($rec['attachments'][0]['url'], $media_1->getUrl());
        $this->assertEquals($rec['attachments'][0]['name'], $media_1->miscs['file']['name']);
        $this->assertEquals($rec['attachments'][0]['size'], $media_1->miscs['file']['size']);

        $this->assertEquals($rec['attachments'][1]['url'], $media_2->getUrl());
        $this->assertEquals($rec['attachments'][1]['name'], $media_2->miscs['file']['name']);
        $this->assertEquals($rec['attachments'][1]['size'], $media_2->miscs['file']['size']);
    }
}
