<?php

namespace Tests\Feature\Orders\Notes;

use App\Enums\Communications\Type;
use App\Models\Communications\CommunicationRecord;
use App\Models\Division;
use App\Models\Order;
use App\Services\Communications\FormatterService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Divisions\DivisionBuilder;
use Tests\Builders\Orders\OrderBuilder;
use Tests\Builders\Users\UserBuilder;
use Tests\TestCase;

class SaveTest extends TestCase
{
    use DatabaseTransactions;

    protected DivisionBuilder $divisionBuilder;
    protected UserBuilder $userBuilder;
    protected OrderBuilder $orderBuilder;

    protected array $data;

    public function setUp(): void
    {
        $this->divisionBuilder = resolve(DivisionBuilder::class);
        $this->userBuilder = resolve(UserBuilder::class);
        $this->orderBuilder = resolve(OrderBuilder::class);

        $this->data = [
            'text' => 'some note test',
            'is_pinned' => 1,
            'returnFormat' => 'communicationPanel',
        ];

        parent::setUp();
    }

    /** @test */
    public function success_return_format_as_communicationPanel()
    {
        $user = $this->loginUser();
        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $this->session(['division' => $division->toArray()]);

        /** @var $order Order */
        $order = $this->orderBuilder->create();

        $data = $this->data;
        $data['order_id'] = $order->id;

        $this->assertEmpty($order->notes);
        $this->assertNull(CommunicationRecord::first());

        $res = $this->post(route('orders.notes.save'), $data)
            ->assertJsonStructure([
                'record' => [
                    'id',
                    'type',
                    'datetime',
                    'uid',
                    'item' => [
                        'order_id',
                        'user_id',
                        'text',
                        'is_pinned',
                        'updated_at',
                        'created_at',
                        'id',
                        'order' => [
                            'id'
                        ],
                        'author' => [
                            'id',
                            'name',
                            'employee',
                        ]
                    ]
                ]
            ])
            ->json('record')
        ;

        $order->refresh();
        /** @var $note Order\Notes */
        $note = $order->notes->first();

        $this->assertEquals($res['type'], FormatterService::getType($note));
        $this->assertEquals($res['uid'], FormatterService::getUid($note));
        $this->assertEquals($res['item']['order_id'], $order->id);
        $this->assertEquals($res['item']['user_id'], $user->id);
        $this->assertEquals($res['item']['text'], $data['text']);
        $this->assertEquals($res['item']['is_pinned'], $data['is_pinned']);

        /** @var $rec CommunicationRecord */
        $rec = CommunicationRecord::first();

        $this->assertEquals($rec->entity_id, $note->id);
        $this->assertEquals($rec->entity_type, Order\Notes::MORPH_NAME);
        $this->assertEquals($rec->client_id, $order->client_id);
        $this->assertEquals($rec->type, Type::Inner);
        $this->assertTrue($rec->is_answered);
        $this->assertNull($rec->channel_contact);
        $this->assertNull($rec->client_ids);
        $this->assertEquals($rec->sort_at, $note->created_at);
    }

    /** @test */
    public function success_without_return_format()
    {
        $user = $this->loginUser();
        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $this->session(['division' => $division->toArray()]);

        /** @var $order Order */
        $order = $this->orderBuilder->create();

        $data = $this->data;
        $data['order_id'] = $order->id;
        unset($data['returnFormat']);


        $this->assertEmpty($order->notes);
        $this->assertNull(CommunicationRecord::first());

        $res = $this->post(route('orders.notes.save'), $data)
            ->assertJsonStructure([
                'records' => [
                    [
                        'id',
                        'order_id',
                        'user_id',
                        'is_pinned',
                        'visibility',
                        'text',
                        'created_at',
                        'updated_at',
                        'deleted_at',
                    ]
                ],
                'success'
            ])
            ->json('records.0')
        ;

        $order->refresh();
        /** @var $note Order\Notes */
        $note = $order->notes->first();

        $this->assertEquals($res['id'], $note->id);
        $this->assertEquals($res['order_id'], $order->id);
        $this->assertEquals($res['user_id'], $user->id);
        $this->assertEquals($res['text'], $data['text']);
        $this->assertEquals($res['is_pinned'], $data['is_pinned']);

        /** @var $rec CommunicationRecord */
        $rec = CommunicationRecord::first();

        $this->assertEquals($rec->entity_id, $note->id);
        $this->assertEquals($rec->entity_type, Order\Notes::MORPH_NAME);
    }
}
