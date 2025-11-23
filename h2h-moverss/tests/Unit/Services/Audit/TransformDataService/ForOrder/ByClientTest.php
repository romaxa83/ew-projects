<?php

namespace Tests\Unit\Services\Audit\TransformDataService\ForOrder;

use App\Models\Audit;
use App\Models\Client;
use App\Services\Audit\TransformDataService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Audits\AuditBuilder;
use Tests\Builders\Clients\ClientBuilder;
use Tests\Builders\Clients\EmailBuilder;
use Tests\Builders\Clients\MessengerBuilder;
use Tests\Builders\Clients\MessengerTypeBuilder;
use Tests\Builders\Clients\NoteBuilder;
use Tests\Builders\Clients\PhoneBuilder;
use Tests\TestCase;

class ByClientTest extends TestCase
{
    use DatabaseTransactions;

    protected ClientBuilder $clientBuilder;
    protected PhoneBuilder $clientPhoneBuilder;
    protected EmailBuilder $clientEmailBuilder;
    protected MessengerBuilder $clientMessengerBuilder;
    protected MessengerTypeBuilder $clientMessengerTypeBuilder;
    protected NoteBuilder $clientNoteBuilder;
    protected AuditBuilder $auditBuilder;

    protected TransformDataService $service;

    public function setUp(): void
    {
        $this->clientBuilder = resolve(ClientBuilder::class);
        $this->clientPhoneBuilder = resolve(PhoneBuilder::class);
        $this->clientEmailBuilder = resolve(EmailBuilder::class);
        $this->clientMessengerBuilder = resolve(MessengerBuilder::class);
        $this->clientMessengerTypeBuilder = resolve(MessengerTypeBuilder::class);
        $this->clientNoteBuilder = resolve(NoteBuilder::class);
        $this->auditBuilder = resolve(AuditBuilder::class);

        $this->service = resolve(TransformDataService::class);


        parent::setUp();
    }

    /** @test */
    public function audit_client_change_field_name()
    {
        $valueNew = 'name new';
        $valueOld = 'name old';

        /** @var $model Client */
        $model = $this->clientBuilder->create();

        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($model)
            ->new_values(['name' => $valueNew])
            ->old_values(['name' => $valueOld])
            ->create();

        $res = $this->service
            ->forOrder($audit);

        $this->assertEquals(1, count($res));
        $this->assertEquals(1, count($res[0]['details']));

        $this->assertEquals($res[0]['details'][0]['field'], 'name');
        $this->assertEquals($res[0]['details'][0]['new'], $valueNew);
        $this->assertEquals($res[0]['details'][0]['old'], $valueOld);

        $this->assertEquals($res[0]['action'], Audit::EVENT_UPDATED);
        $this->assertEquals($res[0]['entity'], 'Client');
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);
        $this->assertFalse($res[0]['is_client_activity']);
        $this->assertNull($res[0]['client']);
    }

    /** @test */
    public function audit_client_change_field_lname()
    {
        $valueNew = 'name new';
        $valueOld = 'name old';

        /** @var $model Client */
        $model = $this->clientBuilder->create();

        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($model)
            ->new_values(['lname' => $valueNew])
            ->old_values(['lname' => $valueOld])
            ->is_client_activity(true)
            ->create();

        $res = $this->service
            ->forOrder($audit);

        $this->assertEquals(1, count($res));
        $this->assertEquals(1, count($res[0]['details']));

        $this->assertEquals($res[0]['details'][0]['field'], 'last name');
        $this->assertEquals($res[0]['details'][0]['new'], $valueNew);
        $this->assertEquals($res[0]['details'][0]['old'], $valueOld);

        $this->assertEquals($res[0]['action'], Audit::EVENT_UPDATED);
        $this->assertEquals($res[0]['entity'], 'Client');
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);

        $this->assertTrue($res[0]['is_client_activity']);
        $this->assertEquals($res[0]['client']->id, $audit->client_id);
    }

    /** @test */
    public function audit_client_change_field_phone()
    {
        $valueNew = '1111111111';
        $valueOld = '2222222222';

        /** @var $model Client\Phone */
        $model = $this->clientPhoneBuilder->create();

        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($model)
            ->new_values(['value' => $valueNew])
            ->old_values(['value' => $valueOld])
            ->create();

        $res = $this->service
            ->forOrder($audit);

        $this->assertEquals(1, count($res));
        $this->assertEquals(1, count($res[0]['details']));

        $this->assertEquals($res[0]['details'][0]['field'], 'phone');
        $this->assertEquals($res[0]['details'][0]['new'], $valueNew);
        $this->assertEquals($res[0]['details'][0]['old'], $valueOld);

        $this->assertEquals($res[0]['action'], Audit::EVENT_UPDATED);
        $this->assertEquals($res[0]['entity'], 'Client');
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);

        $this->assertFalse($res[0]['is_client_activity']);
        $this->assertNull($res[0]['client']);
    }

    /** @test */
    public function audit_client_change_field_phone_type()
    {
        $types = config('app.phone_types');
        $valueNew = '3';
        $valueOld = '7';

        /** @var $model Client\Phone */
        $model = $this->clientPhoneBuilder->create();

        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($model)
            ->new_values(['type_id' => $valueNew])
            ->old_values(['type_id' => $valueOld])
            ->create();

        $res = $this->service->forOrder($audit);

        $this->assertEquals(1, count($res));
        $this->assertEquals(1, count($res[0]['details']));

        $this->assertEquals($res[0]['details'][0]['field'], 'phone type');
        $this->assertEquals($res[0]['details'][0]['new'], $types[$valueNew]);
        $this->assertEquals($res[0]['details'][0]['old'], $types[$valueOld]);

        $this->assertEquals($res[0]['action'], Audit::EVENT_UPDATED);
        $this->assertEquals($res[0]['entity'], 'Client');
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);

        $this->assertFalse($res[0]['is_client_activity']);
        $this->assertNull($res[0]['client']);
    }

    /** @test */
    public function audit_client_change_field_phone_type_if_wrong_type()
    {
        $types = config('app.phone_types');
        $valueNew = '33';
        $valueOld = '7';

        /** @var $model Client\Phone */
        $model = $this->clientPhoneBuilder->create();

        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($model)
            ->new_values(['type_id' => $valueNew])
            ->old_values(['type_id' => $valueOld])
            ->create();

        $res = $this->service->forOrder($audit);

        $this->assertEquals(1, count($res));
        $this->assertEquals(1, count($res[0]['details']));

        $this->assertEquals($res[0]['details'][0]['field'], 'phone type');
        $this->assertNull($res[0]['details'][0]['new']);
        $this->assertEquals($res[0]['details'][0]['old'], $types[$valueOld]);

        $this->assertEquals($res[0]['action'], Audit::EVENT_DELETED);
        $this->assertEquals($res[0]['entity'], 'Client');
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);

        $this->assertFalse($res[0]['is_client_activity']);
        $this->assertNull($res[0]['client']);
    }

    /** @test */
    public function audit_client_add_field_new_phone()
    {
        $types = config('app.phone_types');
        $valueNew = '3';

        /** @var $model Client\Phone */
        $model = $this->clientPhoneBuilder->create();

        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($model)
            ->new_values([
                'type_id' => $valueNew,
                'client_id' => $model->client_id,
                'is_primary' => 0,
                'value' => $model->value,
                'id' => $model->id,
            ])
            ->create();

        $res = $this->service->forOrder($audit);

        $this->assertEquals(2, count($res));
        $this->assertEquals(1, count($res[0]['details']));
        $this->assertEquals(1, count($res[1]['details']));

        $this->assertEquals($res[0]['details'][0]['field'], 'phone type');
        $this->assertNull($res[0]['details'][0]['old']);
        $this->assertEquals($res[0]['details'][0]['new'], $types[$valueNew]);

        $this->assertEquals($res[0]['action'], Audit::EVENT_CREATED);
        $this->assertEquals($res[0]['entity'], 'Client');
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);
        $this->assertFalse($res[0]['is_client_activity']);
        $this->assertNull($res[0]['client']);

        $this->assertEquals($res[1]['details'][0]['field'], 'phone');
        $this->assertNull($res[1]['details'][0]['old']);
        $this->assertEquals($res[1]['details'][0]['new'], $model->value);
    }

    /** @test */
    public function audit_client_delete_field_phone()
    {
        /** @var $model Client\Phone */
        $model = $this->clientPhoneBuilder->create();

        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->event(Audit::EVENT_DELETED)
            ->auditable($model)
            ->old_values([
                'type_id' => $model->type_id,
                'client_id' => $model->client_id,
                'is_primary' => 0,
                'sort' => 0,
                'value' => $model->value,
                'id' => $model->id,
            ])
            ->create();

        $res = $this->service->forOrder($audit);

        $this->assertEquals(1, count($res));
        $this->assertEquals(1, count($res[0]['details']));

        $this->assertEquals($res[0]['details'][0]['field'], 'phone');
        $this->assertNull($res[0]['details'][0]['new']);
        $this->assertEquals($res[0]['details'][0]['old'], $model->value);

        $this->assertEquals($res[0]['action'], Audit::EVENT_DELETED);
        $this->assertEquals($res[0]['entity'], 'Client');
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);
        $this->assertFalse($res[0]['is_client_activity']);
        $this->assertNull($res[0]['client']);
    }

    /** @test */
    public function audit_client_change_field_email()
    {
        $valueNew = 'new@gmail.com';
        $valueOld = 'old@gmail.com';

        /** @var $model Client\Email */
        $model = $this->clientEmailBuilder->create();

        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($model)
            ->new_values(['value' => $valueNew])
            ->old_values(['value' => $valueOld])
            ->create();

        $res = $this->service->forOrder($audit);

        $this->assertEquals(1, count($res));
        $this->assertEquals(1, count($res[0]['details']));

        $this->assertEquals($res[0]['details'][0]['field'], 'email');
        $this->assertEquals($res[0]['details'][0]['new'], $valueNew);
        $this->assertEquals($res[0]['details'][0]['old'], $valueOld);

        $this->assertEquals($res[0]['action'], Audit::EVENT_UPDATED);
        $this->assertEquals($res[0]['entity'], 'Client');
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);
        $this->assertFalse($res[0]['is_client_activity']);
        $this->assertNull($res[0]['client']);
    }

    /** @test */
    public function audit_client_add_field_email()
    {
        /** @var $model Client\Email */
        $model = $this->clientEmailBuilder->create();

        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($model)
            ->new_values([
                'client_id' => $model->client_id,
                'value' => $model->value,
                'id' => $model->id
            ])
            ->create();

        $res = $this->service->forOrder($audit);

        $this->assertEquals(1, count($res));
        $this->assertEquals(1, count($res[0]['details']));

        $this->assertEquals($res[0]['details'][0]['field'], 'email');
        $this->assertEquals($res[0]['details'][0]['new'], $model->value);
        $this->assertNull($res[0]['details'][0]['old']);

        $this->assertEquals($res[0]['action'], Audit::EVENT_CREATED);
        $this->assertEquals($res[0]['entity'], 'Client');
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);
        $this->assertFalse($res[0]['is_client_activity']);
        $this->assertNull($res[0]['client']);
    }

    /** @test */
    public function audit_client_delete_field_email()
    {
        /** @var $model Client\Email */
        $model = $this->clientEmailBuilder->create();

        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->event(Audit::EVENT_DELETED)
            ->auditable($model)
            ->old_values([
                'type_id' => $model->type_id,
                'client_id' => $model->client_id,
                'is_primary' => 0,
                'sort' => 0,
                'value' => $model->value,
                'id' => $model->id,
            ])
            ->create();

        $res = $this->service->forOrder($audit);

        $this->assertEquals(1, count($res));
        $this->assertEquals(1, count($res[0]['details']));

        $this->assertEquals($res[0]['details'][0]['field'], 'email');
        $this->assertEquals($res[0]['details'][0]['old'], $model->value);
        $this->assertNull($res[0]['details'][0]['new']);

        $this->assertEquals($res[0]['action'], Audit::EVENT_DELETED);
        $this->assertEquals($res[0]['entity'], 'Client');
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);
        $this->assertFalse($res[0]['is_client_activity']);
        $this->assertNull($res[0]['client']);
    }

    /** @test */
    public function audit_client_change_field_messenger()
    {
        $valueNew = '111111';
        $valueOld = '222222';

        /** @var $model Client\Messenger */
        $model = $this->clientMessengerBuilder->create();

        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($model)
            ->new_values(['value' => $valueNew])
            ->old_values(['value' => $valueOld])
            ->create();

        $res = $this->service->forOrder($audit);

        $this->assertEquals(1, count($res));
        $this->assertEquals(1, count($res[0]['details']));

        $this->assertEquals($res[0]['details'][0]['field'], 'messenger');
        $this->assertEquals($res[0]['details'][0]['new'], $valueNew);
        $this->assertEquals($res[0]['details'][0]['old'], $valueOld);

        $this->assertEquals($res[0]['action'], Audit::EVENT_UPDATED);
        $this->assertEquals($res[0]['entity'], 'Client');
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);
        $this->assertFalse($res[0]['is_client_activity']);
        $this->assertNull($res[0]['client']);
    }

    /** @test */
    public function audit_client_add_field_messenger()
    {
        /** @var $model Client\Messenger */
        $model = $this->clientMessengerBuilder->create();

        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($model)
            ->new_values([
                'client_id' => $model->client_id,
                'value' => $model->value,
                'id' => $model->id,
                'type_id' => $model->type_id,
            ])
            ->create();

        $res = $this->service->forOrder($audit);

        $this->assertEquals(2, count($res));
        $this->assertEquals(1, count($res[0]['details']));

        $this->assertEquals($res[0]['details'][0]['field'], 'messenger');
        $this->assertEquals($res[0]['details'][0]['new'], $model->value);
        $this->assertNull($res[0]['details'][0]['old']);

        $this->assertEquals($res[0]['action'], Audit::EVENT_CREATED);
        $this->assertEquals($res[0]['entity'], 'Client');
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);
        $this->assertFalse($res[0]['is_client_activity']);
        $this->assertNull($res[0]['client']);

        $this->assertEquals($res[1]['details'][0]['field'], 'messenger type');
        $this->assertEquals($res[1]['details'][0]['new'], $model->type->title);
        $this->assertNull($res[1]['details'][0]['old']);
    }

    /** @test */
    public function audit_client_change_field_messenger_type()
    {
        $typeOld = $this->clientMessengerTypeBuilder->create();
        $typeNew = $this->clientMessengerTypeBuilder->create();

        /** @var $model Client\Messenger */
        $model = $this->clientMessengerBuilder->create();

        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($model)
            ->new_values([
                'type_id' => $typeNew->id,
            ])
            ->old_values([
                'type_id' => $typeOld->id,
            ])
            ->create();

        $res = $this->service->forOrder($audit);

        $this->assertEquals(1, count($res));
        $this->assertEquals(1, count($res[0]['details']));

        $this->assertEquals($res[0]['details'][0]['field'], 'messenger type');
        $this->assertEquals($res[0]['details'][0]['new'], $typeNew->title);
        $this->assertEquals($res[0]['details'][0]['old'], $typeOld->title);

        $this->assertEquals($res[0]['action'], Audit::EVENT_UPDATED);
        $this->assertEquals($res[0]['entity'], 'Client');
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);
        $this->assertFalse($res[0]['is_client_activity']);
        $this->assertNull($res[0]['client']);
    }

    /** @test */
    public function audit_client_delete_field_messenger()
    {
        /** @var $model Client\Messenger */
        $model = $this->clientMessengerBuilder->create();

        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->event(Audit::EVENT_DELETED)
            ->auditable($model)
            ->old_values([
                'type_id' => $model->type_id,
                'client_id' => $model->client_id,
                'value' => $model->value,
                'id' => $model->id,
            ])
            ->create();

        $res = $this->service->forOrder($audit);

        $this->assertEquals(1, count($res));
        $this->assertEquals(1, count($res[0]['details']));

        $this->assertEquals($res[0]['details'][0]['field'], 'messenger');
        $this->assertNull($res[0]['details'][0]['new']);
        $this->assertEquals($res[0]['details'][0]['old'], $model->value);

        $this->assertEquals($res[0]['action'], Audit::EVENT_DELETED);
        $this->assertEquals($res[0]['entity'], 'Client');
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);
        $this->assertFalse($res[0]['is_client_activity']);
        $this->assertNull($res[0]['client']);
    }

    /** @test */
    public function audit_client_add_field_note()
    {
        /** @var $model Client\Notes */
        $model = $this->clientNoteBuilder->create();

        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($model)
            ->new_values([
                'client_id' => $model->client_id,
                'user_id' => $model->user_id,
                'value' => $model->value,
                'id' => $model->id,
            ])
            ->create();

        $res = $this->service->forOrder($audit);

        $this->assertEquals(1, count($res));
        $this->assertEquals(1, count($res[0]['details']));

        $this->assertEquals($res[0]['details'][0]['field'], 'note');
        $this->assertNull($res[0]['details'][0]['old']);
        $this->assertEquals($res[0]['details'][0]['new'], $model->value);

        $this->assertEquals($res[0]['action'], Audit::EVENT_CREATED);
        $this->assertEquals($res[0]['entity'], 'Client');
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);
        $this->assertFalse($res[0]['is_client_activity']);
        $this->assertNull($res[0]['client']);
    }

    /** @test */
    public function audit_client_change_field_note()
    {
        /** @var $model Client\Notes */
        $model = $this->clientNoteBuilder->create();

        $newValue = 'text';
        $oldValue = 'text old';

        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($model)
            ->new_values([
                'value' => $newValue,
            ])
            ->old_values([
                'value' => $oldValue,
            ])
            ->create();

        $res = $this->service->forOrder($audit);

        $this->assertEquals(1, count($res));
        $this->assertEquals(1, count($res[0]['details']));

        $this->assertEquals($res[0]['details'][0]['field'], 'note');
        $this->assertEquals($res[0]['details'][0]['new'], $newValue);
        $this->assertEquals($res[0]['details'][0]['old'], $oldValue);

        $this->assertEquals($res[0]['action'], Audit::EVENT_UPDATED);
        $this->assertEquals($res[0]['entity'], 'Client');
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);
        $this->assertFalse($res[0]['is_client_activity']);
        $this->assertNull($res[0]['client']);
    }

    /** @test */
    public function audit_client_delete_field_note()
    {
        /** @var $model Client\Notes */
        $model = $this->clientNoteBuilder->create();

        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($model)
            ->event(Audit::EVENT_DELETED)
            ->old_values([
                'client_id' => $model->client_id,
                'user_id' => $model->user_id,
                'value' => $model->value,
                'id' => $model->id,
            ])
            ->create();

        $res = $this->service->forOrder($audit);

        $this->assertEquals(1, count($res));
        $this->assertEquals(1, count($res[0]['details']));

        $this->assertEquals($res[0]['details'][0]['field'], 'note');
        $this->assertNull($res[0]['details'][0]['new']);
        $this->assertEquals($res[0]['details'][0]['old'], $model->value);

        $this->assertEquals($res[0]['action'], Audit::EVENT_DELETED);
        $this->assertEquals($res[0]['entity'], 'Client');
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);
        $this->assertFalse($res[0]['is_client_activity']);
        $this->assertNull($res[0]['client']);
    }

    /** @test */
    public function audit_client_add_field_tags()
    {
        /** @var $model Client */
        $model = $this->clientBuilder->create();

        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($model)
            ->event(Audit::EVENT_SYNC)
            ->old_values([
                'tags' => [],
                'custom_tags' => []
            ])
            ->new_values([
                'tags' => [
                    [
                        'id' => 1,
                        'color' => "",
                        'title' => "tag 1",
                    ],
                    [
                        'id' => 2,
                        'color' => "red",
                        'title' => "tag 2",
                    ]
                ],
                'custom_tags' => [
                    'tag 1', 'tag 2'
                ]
            ])
            ->create();

        $res = $this->service->forOrder($audit);

        $this->assertEquals(1, count($res));
        $this->assertEquals(1, count($res[0]['details']));

        $this->assertEquals($res[0]['details'][0]['field'], 'tags');
        $this->assertNull($res[0]['details'][0]['old']);
        $this->assertEquals($res[0]['details'][0]['new'], "tag 1, tag 2");

        $this->assertEquals($res[0]['action'], Audit::EVENT_CREATED);
        $this->assertEquals($res[0]['entity'], 'Client');
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);
        $this->assertFalse($res[0]['is_client_activity']);
        $this->assertNull($res[0]['client']);
    }

    /** @test */
    public function audit_client_change_field_tags()
    {
        /** @var $model Client */
        $model = $this->clientBuilder->create();

        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($model)
            ->event(Audit::EVENT_SYNC)
            ->old_values([
                'tags' => [],
                'custom_tags' => [
                    'tag 1', 'tag 2'
                ]
            ])
            ->new_values([
                'tags' => [
                    [
                        'id' => 1,
                        'color' => "",
                        'title' => "tag 1",
                    ],
                    [
                        'id' => 2,
                        'color' => "red",
                        'title' => "tag 2",
                    ]
                ],
                'custom_tags' => [
                    'tag 2'
                ]
            ])
            ->create();

        $res = $this->service->forOrder($audit);

        $this->assertEquals(1, count($res));
        $this->assertEquals(1, count($res[0]['details']));

        $this->assertEquals($res[0]['details'][0]['field'], 'tags');
        $this->assertEquals($res[0]['details'][0]['old'], "tag 1, tag 2");
        $this->assertEquals($res[0]['details'][0]['new'], "tag 2");

        $this->assertEquals($res[0]['action'], Audit::EVENT_UPDATED);
        $this->assertEquals($res[0]['entity'], 'Client');
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);
        $this->assertFalse($res[0]['is_client_activity']);
        $this->assertNull($res[0]['client']);
    }

    /** @test */
    public function audit_client_delete_field_tags()
    {
        /** @var $model Client */
        $model = $this->clientBuilder->create();

        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($model)
            ->event(Audit::EVENT_SYNC)
            ->old_values([
                'tags' => [],
                'custom_tags' => [
                    'tag 1', 'tag 2'
                ]
            ])
            ->new_values([
                'tags' => [
                    [
                        'id' => 1,
                        'color' => "",
                        'title' => "tag 1",
                    ],
                    [
                        'id' => 2,
                        'color' => "red",
                        'title' => "tag 2",
                    ]
                ],
                'custom_tags' => []
            ])
            ->create();

        $res = $this->service->forOrder($audit);

        $this->assertEquals(1, count($res));
        $this->assertEquals(1, count($res[0]['details']));

        $this->assertEquals($res[0]['details'][0]['field'], 'tags');
        $this->assertEquals($res[0]['details'][0]['old'], "tag 1, tag 2");
        $this->assertNull($res[0]['details'][0]['new']);

        $this->assertEquals($res[0]['action'], Audit::EVENT_DELETED);
        $this->assertEquals($res[0]['entity'], 'Client');
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);
        $this->assertFalse($res[0]['is_client_activity']);
        $this->assertNull($res[0]['client']);
    }
}
