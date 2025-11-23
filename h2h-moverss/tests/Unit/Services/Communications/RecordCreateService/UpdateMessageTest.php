<?php

namespace Tests\Unit\Services\Communications\RecordCreateService;

use App\Models\Communications\CommunicationRecord;
use App\Models\Mailbox\Gmail\Message;
use App\Services\Communications\RecordCreateService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Communications\CommunicationRecordBuilder;
use Tests\Builders\Gmail\MessageBuilder;
use Tests\TestCase;

class UpdateMessageTest extends TestCase
{
    use DatabaseTransactions;
    protected MessageBuilder $gmailMessageBuilder;
    protected CommunicationRecordBuilder $communicationRecordBuilder;

    public function setUp(): void
    {
        $this->communicationRecordBuilder = resolve(CommunicationRecordBuilder::class);
        $this->gmailMessageBuilder = resolve(MessageBuilder::class);

        parent::setUp();
    }

    /** @test */
    public function update()
    {
        /** @var $model Message */
        $model = $this->gmailMessageBuilder
            ->tag(Message::TAG_SENT)
            ->create();

        $record = $this->communicationRecordBuilder
            ->entity($model)
            ->sort_at($model->updated_at)
            ->create();

        $now = CarbonImmutable::now()->addDay();
        CarbonImmutable::setTestNow($now);

        $model->updated_at = CarbonImmutable::now()->addDay();
        $model->save();

        $this->assertNotEquals($record->sort_at->timestamp, $model->updated_at->timestamp);

        $rec = RecordCreateService::updatedMessage($model);

        $this->assertEquals($rec->sort_at->timestamp, $model->updated_at->timestamp);
    }

    /** @test */
    public function update_if_not_record()
    {
        /** @var $model Message */
        $model = $this->gmailMessageBuilder
            ->tag(Message::TAG_SENT)
            ->create();

        CommunicationRecord::query()
            ->where('entity_id', $model->id)
            ->delete();

        $rec = RecordCreateService::updatedMessage($model);

        $this->assertNull($rec);
    }
}
