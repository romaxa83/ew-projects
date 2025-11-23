<?php

namespace Tests\Feature\Orders\Order;

use App\Enums\Catalog\MoveSizeTypeEnum;
use App\Enums\Communications\Type;
use App\Enums\Orders\ActivityType;
use App\Models\Audit;
use App\Models\Communications\CommunicationRecord;
use App\Models\Division;
use App\Models\Order;
use App\Models\Order\Source;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Divisions\DivisionBuilder;
use Tests\Builders\Orders\OrderBuilder;
use Tests\Builders\Orders\SourceBuilder;
use Tests\Builders\Orders\StatusBuilder;
use Tests\Builders\Orders\TagBuilder;
use Tests\Builders\Users\UserBuilder;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    use DatabaseTransactions;

    protected DivisionBuilder $divisionBuilder;
    protected UserBuilder $userBuilder;
    protected OrderBuilder $orderBuilder;
    protected StatusBuilder $statusBuilder;
    protected SourceBuilder $sourceBuilder;
    protected TagBuilder $tagBuilder;

    public function setUp(): void
    {
        $this->divisionBuilder = resolve(DivisionBuilder::class);
        $this->userBuilder = resolve(UserBuilder::class);
        $this->orderBuilder = resolve(OrderBuilder::class);
        $this->statusBuilder = resolve(StatusBuilder::class);
        $this->sourceBuilder = resolve(SourceBuilder::class);
        $this->tagBuilder = resolve(TagBuilder::class);

        parent::setUp();
    }

    /** @test */
    public function update_division()
    {
        $user = $this->loginUser();
        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $this->session(['division' => $division->toArray()]);

        /** @var $model Order */
        $model = $this->orderBuilder
            ->create();

        $data = [
            'division_id' => $division->id,
        ];

        $oldDivisionId = $model->division_id;

        $this->assertNotEquals($model->division_id, $division->id);
        $this->assertEmpty($model->activities);
        $this->assertNull($model->updated_by);
        $this->assertNull(CommunicationRecord::first());

        $this->post(route('orders.record.order', [
            'id' => $model->id,
        ]), $data)
            ->assertJson([
                'success' => true,
                'msg' => "Order changed",
            ])
        ;

        $model->refresh();

        $this->assertEquals($model->division_id, $division->id);
        $this->assertEquals($model->updated_by, $user->id);

        /** @var $activity Order\Activity */
        $activity = $model->activities->first();

        $this->assertEquals($activity->user_id, $user->id);
        $this->assertEquals($activity->type, ActivityType::Division->value);
        $this->assertEquals($activity->miscs['from'], $oldDivisionId);
        $this->assertEquals($activity->miscs['to'], $model->division_id);

        /** @var $rec CommunicationRecord */
        $rec = CommunicationRecord::first();

        $this->assertEquals($rec->entity_id, $activity->id);
        $this->assertEquals($rec->entity_type, Order\Activity::MORPH_NAME);
        $this->assertEquals($rec->client_id, $model->client_id);
        $this->assertEquals($rec->type, Type::Inner);
        $this->assertTrue($rec->is_answered);
        $this->assertNull($rec->channel_contact);
        $this->assertNull($rec->client_ids);
        $this->assertEquals($rec->sort_at, $activity->created_at);
    }

    /** @test */
    public function update_source()
    {
        $user = $this->loginUser();
        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $this->session(['division' => $division->toArray()]);

        /** @var $source Source */
        $source = $this->sourceBuilder->create();

        /** @var $model Order */
        $model = $this->orderBuilder
            ->create();

        $data = [
            'division_id' => $model->division_id,
            'source_id' => $source->id,
        ];

        $this->assertNotEquals($model->source_id, $source->id);
        $this->assertEmpty($model->activities);
        $this->assertNull(CommunicationRecord::first());

        $this->post(route('orders.record.order', [
            'id' => $model->id,
        ]), $data)
            ->assertJson([
                'success' => true,
                'msg' => "Order changed",
            ])
        ;

        $model->refresh();

        $this->assertEquals($model->source_id, $source->id);

        /** @var $activity Order\Activity */
        $activity = $model->activities->first();

        $this->assertEquals($activity->user_id, $user->id);
        $this->assertEquals($activity->type, ActivityType::Source->value);

        /** @var $rec CommunicationRecord */
        $rec = CommunicationRecord::first();

        $this->assertEquals($rec->entity_id, $activity->id);
        $this->assertEquals($rec->entity_type, Order\Activity::MORPH_NAME);
        $this->assertEquals($rec->client_id, $model->client_id);
        $this->assertEquals($rec->type, Type::Inner);
        $this->assertTrue($rec->is_answered);
        $this->assertNull($rec->channel_contact);
        $this->assertNull($rec->client_ids);
        $this->assertEquals($rec->sort_at, $activity->created_at);
    }

    /** @test */
    public function update_type()
    {
        $user = $this->loginUser();
        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $this->session(['division' => $division->toArray()]);

        /** @var $model Order */
        $model = $this->orderBuilder
            ->create();

        $data = [
            'division_id' => $model->division_id,
            'type' => 'house',
        ];

        $this->assertNull($model->type);

        $this->post(route('orders.record.order', [
            'id' => $model->id,
        ]), $data)
            ->assertJson([
                'success' => true,
                'msg' => "Order changed",
            ])
        ;

        $model->refresh();

        $this->assertEquals($model->type, $data['type']);
    }

    /** @test */
    public function update_move_size()
    {
        $this->loginUser();
        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $this->session(['division' => $division->toArray()]);

        /** @var $model Order */
        $model = $this->orderBuilder
            ->create();

        $data = [
            'division_id' => $model->division_id,
            'move_size_id' => MoveSizeTypeEnum::Studio(),
        ];

        $this->assertNull($model->move_size_id);

        $this->post(route('orders.record.order', [
            'id' => $model->id,
        ]), $data)
            ->assertJson([
                'success' => true,
                'msg' => "Order changed",
            ])
        ;

        $model->refresh();

        $this->assertEquals($model->move_size_id, MoveSizeTypeEnum::Studio());
    }

    /** @test */
    public function update_user()
    {
        $user = $this->loginUser();
        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $this->session(['division' => $division->toArray()]);

        /** @var $manager User */
        $manager = $this->userBuilder->create();

        /** @var $model Order */
        $model = $this->orderBuilder
            ->create();

        $data = [
            'division_id' => $model->division_id,
            'user_id' => $manager->id,
        ];

        $this->assertNotEquals($model->user_id, $manager->id);
        $this->assertEmpty($model->activities);
        $this->assertNull(CommunicationRecord::first());

        $this->post(route('orders.record.order', [
            'id' => $model->id,
        ]), $data)
            ->assertJson([
                'success' => true,
                'msg' => "Order changed",
            ])
        ;

        $model->refresh();

        $this->assertEquals($model->user_id, $manager->id);

        /** @var $activity Order\Activity */
        $activity = $model->activities->first();

        $this->assertEquals($activity->user_id, $user->id);
        $this->assertEquals($activity->type, ActivityType::User->value);

        /** @var $rec CommunicationRecord */
        $rec = CommunicationRecord::first();

        $this->assertEquals($rec->entity_id, $activity->id);
        $this->assertEquals($rec->entity_type, Order\Activity::MORPH_NAME);
        $this->assertEquals($rec->client_id, $model->client_id);
        $this->assertEquals($rec->type, Type::Inner);
        $this->assertTrue($rec->is_answered);
        $this->assertNull($rec->channel_contact);
        $this->assertNull($rec->client_ids);
        $this->assertEquals($rec->sort_at, $activity->created_at);
    }

    /** @test */
    public function add_tags_and_check_audit()
    {
        $this->loginUser();
        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $this->session(['division' => $division->toArray()]);

        /** @var $tag_1 Order\Tag */
        $tag_1 = $this->tagBuilder->create();

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $data = [
            'division_id' => $model->division_id,
            'selectedTags' => [
                [
                    'id' => $tag_1->id,
                    'title' => $tag_1->title,
                ]
            ],
        ];

        $this->assertEmpty($model->tags);

        $this->post(route('orders.record.order', [
            'id' => $model->id,
        ]), $data)
            ->assertJson([
                'success' => true,
                'msg' => "Order changed",
            ])
        ;

        $model->refresh();

        $this->assertCount(1, $model->tags);

        $audit = Audit::query()
            ->where('order_id', $model->id)
            ->where('event', Audit::EVENT_SYNC)
            ->first();

        $this->assertEquals(count($audit->old_values['custom_tags']), 0);
        $this->assertEquals($audit->new_values['custom_tags'], [$tag_1->title]);
    }

    /** @test */
    public function add_more_tags_and_check_audit()
    {
        $this->loginUser();
        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $this->session(['division' => $division->toArray()]);

        /** @var $tag_1 Order\Tag */
        $tag_1 = $this->tagBuilder->create();
        $tag_2 = $this->tagBuilder->create();

        /** @var $model Order */
        $model = $this->orderBuilder
            ->tags($tag_1)->create();

        $data = [
            'division_id' => $model->division_id,
            'selectedTags' => [
                [
                    'id' => $tag_1->id,
                    'title' => $tag_1->title,
                ],
                [
                    'id' => $tag_2->id,
                    'title' => $tag_2->title,
                ]
            ],
        ];

        $this->assertCount(1, $model->tags);

        $this->post(route('orders.record.order', [
            'id' => $model->id,
        ]), $data)
            ->assertJson([
                'success' => true,
                'msg' => "Order changed",
            ])
        ;

        $model->refresh();

        $this->assertCount(2, $model->tags);

        $audit = Audit::query()
            ->where('order_id', $model->id)
            ->where('event', Audit::EVENT_SYNC)
            ->first();

        $this->assertEquals($audit->old_values['custom_tags'], [$tag_1->title]);
        $this->assertEquals($audit->new_values['custom_tags'], [$tag_1->title, $tag_2->title]);
    }

    /** @test */
    public function delete_tags_and_check_audit()
    {
        $this->loginUser();
        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $this->session(['division' => $division->toArray()]);

        /** @var $tag_1 Order\Tag */
        $tag_1 = $this->tagBuilder->create();

        /** @var $model Order */
        $model = $this->orderBuilder
            ->tags($tag_1)->create();

        $data = [
            'division_id' => $model->division_id,
            'selectedTags' => [],
        ];

        $this->assertCount(1, $model->tags);

        $this->post(route('orders.record.order', [
            'id' => $model->id,
        ]), $data)
            ->assertJson([
                'success' => true,
                'msg' => "Order changed",
            ])
        ;

        $model->refresh();

        $this->assertCount(0, $model->tags);

        $audit = Audit::query()
            ->where('order_id', $model->id)
            ->where('event', Audit::EVENT_SYNC)
            ->first();

        $this->assertEquals($audit->old_values['custom_tags'], [$tag_1->title]);
        $this->assertEquals(0, count($audit->new_values['custom_tags']));
    }

    /**
     * @dataProvider validate
     * @test
     */
    public function validate_data($field, $value, $msg)
    {
        $this->loginUser();
        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $this->session(['division' => $division->toArray()]);

        /** @var $model Order */
        $model = $this->orderBuilder
            ->create();

        $data['division_id'] = $model->division_id;
        $data[$field] = $value;

        $this->post(route('orders.record.order', [
            'id' => $model->id,
        ]), $data)
            ->assertJson([
                'success' => false,
                'errors' => [
                    $field => [
                        $msg
                    ]
                ],
            ])
        ;
    }

    public static function validate(): array
    {
        return [
            ['type', 'wrong', 'The selected type is invalid.'],
            ['move_size_id', '999', 'The selected move size id is invalid.'],
        ];
    }
}
