<?php

namespace Tests\Unit\Services\Audit\TransformDataService\ForOrder;

use App\Models\Attachment;
use App\Models\Audit;
use App\Services\Audit\TransformDataService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Attachment\AttachmentBuilder;
use Tests\Builders\Audits\AuditBuilder;
use Tests\Builders\Orders\OrderBuilder;
use Tests\TestCase;

class ByOrderAttachmentTest extends TestCase
{
    use DatabaseTransactions;

    protected OrderBuilder $orderBuilder;
    protected AttachmentBuilder $attachmentBuilder;

    protected AuditBuilder $auditBuilder;

    protected TransformDataService $service;

    public function setUp(): void
    {
        $this->attachmentBuilder = resolve(AttachmentBuilder::class);
        $this->orderBuilder = resolve(OrderBuilder::class);
        $this->auditBuilder = resolve(AuditBuilder::class);

        $this->service = resolve(TransformDataService::class);

        parent::setUp();
    }

    /** @test */
    public function audit_attachment_upload()
    {
        /** @var $model Attachment */
        $model = $this->attachmentBuilder->create();

        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($model)
            ->new_values([
                'id' => $model->id,
                'user_id' => 1,
                'hash' => "be01a661a26278479ea30750d499160a65b749c09f6d278d979ca6c742baa953",
                'miscs' => "{\"object\":{\"type\":\"order\",\"id\":144949},\"file\":{\"patch\":\"attachments\\\/order\\\/144\\\/\",\"size\":\"37.57 kB\",\"name\":\"photo_2024-04-17_15-18-19.jpg\"}}",
                'description' => "desc",
            ])
            ->create();

        $res = $this->service
            ->forOrder($audit);

        $this->assertEquals(1, count($res));
        $this->assertEquals(2, count($res[0]['details']));

        $this->assertEquals($res[0]['details'][0]['field'], 'description');
        $this->assertNull($res[0]['details'][0]['old']);
        $this->assertEquals($res[0]['details'][0]['new'], 'desc');

        $this->assertEquals($res[0]['details'][1]['field'], 'name');
        $this->assertNull($res[0]['details'][1]['old']);
        $this->assertEquals($res[0]['details'][1]['new'], 'photo_2024-04-17_15-18-19.jpg');

        $this->assertEquals($res[0]['action'], Audit::EVENT_CREATED);
        $this->assertEquals($res[0]['entity'], 'File');
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);
        $this->assertFalse($res[0]['is_client_activity']);
        $this->assertNull($res[0]['client']);
    }

    /** @test */
    public function audit_attachment_upload_without_desc()
    {
        /** @var $model Attachment */
        $model = $this->attachmentBuilder->create();

        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($model)
            ->new_values([
                'id' => $model->id,
                'user_id' => 1,
                'hash' => "be01a661a26278479ea30750d499160a65b749c09f6d278d979ca6c742baa953",
                'miscs' => "{\"object\":{\"type\":\"order\",\"id\":144949},\"file\":{\"patch\":\"attachments\\\/order\\\/144\\\/\",\"size\":\"37.57 kB\",\"name\":\"photo_2024-04-17_15-18-19.jpg\"}}",
            ])
            ->create();

        $res = $this->service
            ->forOrder($audit);

        $this->assertEquals(1, count($res));
        $this->assertEquals(1, count($res[0]['details']));

        $this->assertEquals($res[0]['details'][0]['field'], 'name');
        $this->assertNull($res[0]['details'][0]['old']);
        $this->assertEquals($res[0]['details'][0]['new'], 'photo_2024-04-17_15-18-19.jpg');

        $this->assertEquals($res[0]['action'], Audit::EVENT_CREATED);
        $this->assertEquals($res[0]['entity'], 'File');
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);
        $this->assertFalse($res[0]['is_client_activity']);
        $this->assertNull($res[0]['client']);
    }

    /** @test */
    public function audit_attachment_delete()
    {
        /** @var $model Attachment */
        $model = $this->attachmentBuilder->create();

        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($model)
            ->event(Audit::EVENT_DELETED)
            ->old_values([
                'id' => $model->id,
                'user_id' => 1,
                'hash' => "be01a661a26278479ea30750d499160a65b749c09f6d278d979ca6c742baa953",
                'miscs' => "{\"object\":{\"type\":\"order\",\"id\":144949},\"file\":{\"patch\":\"attachments\\\/order\\\/144\\\/\",\"size\":\"37.57 kB\",\"name\":\"photo_2024-04-17_15-18-19.jpg\"}}",
                'description' => "desc",
            ])
            ->create();

        $res = $this->service
            ->forOrder($audit);

        $this->assertEquals(1, count($res));
        $this->assertEquals(1, count($res[0]['details']));

        $this->assertEquals($res[0]['details'][0]['field'], 'name');
        $this->assertNull($res[0]['details'][0]['new']);
        $this->assertEquals($res[0]['details'][0]['old'], 'photo_2024-04-17_15-18-19.jpg');

        $this->assertEquals($res[0]['action'], Audit::EVENT_DELETED);
        $this->assertEquals($res[0]['entity'], 'File');
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);
        $this->assertFalse($res[0]['is_client_activity']);
        $this->assertNull($res[0]['client']);
    }
}
