<?php

namespace Tests\Unit\Models\Audits;

use App\Models\Audit;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Audits\AuditBuilder;
use Tests\TestCase;

class AuditTest extends TestCase
{
    use DatabaseTransactions;

    protected AuditBuilder $auditBuilder;

    public function setUp(): void
    {
        $this->auditBuilder = resolve(AuditBuilder::class);


        parent::setUp();
    }

    /** @test */
    public function check_is_event_deleted()
    {
        /** @var $model Audit */
        $model = $this->auditBuilder
            ->event(Audit::EVENT_DELETED)
            ->create();

        $this->assertTrue($model->isEventDeleted());
    }

    /** @test */
    public function check_pretty_values_all_data()
    {
        /** @var $model Audit */
        $model = $this->auditBuilder
            ->old_values([
                'field_1' => 'old_value_1',
                'field_2' => 'old_value_2',
            ])
            ->new_values([
                'field_1' => 'new_value_1',
                'field_2' => 'new_value_2',
            ])
            ->create();

        $this->assertEquals($model->getPrettyValues(), [
            [
                'field' => 'field_1',
                'new' => 'new_value_1',
                'old' => 'old_value_1',
            ],
            [
                'field' => 'field_2',
                'new' => 'new_value_2',
                'old' => 'old_value_2',
            ]
        ]);
    }

    /** @test */
    public function check_pretty_values_has_only_new_data()
    {
        /** @var $model Audit */
        $model = $this->auditBuilder
            ->new_values([
                'field_1' => 'new_value_1',
                'field_2' => 'new_value_2',
            ])
            ->create();

        $this->assertEquals($model->getPrettyValues(), [
            [
                'field' => 'field_1',
                'new' => 'new_value_1',
                'old' => null,
            ],
            [
                'field' => 'field_2',
                'new' => 'new_value_2',
                'old' => null,
            ]
        ]);
    }

    /** @test */
    public function check_pretty_values_not_data()
    {
        /** @var $model Audit */
        $model = $this->auditBuilder
            ->create();

        $this->assertEmpty($model->getPrettyValues());
    }
}
