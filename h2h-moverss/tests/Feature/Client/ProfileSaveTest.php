<?php

namespace Tests\Feature\Client;

use App\Models\Audit;
use App\Models\Client;
use App\Models\Division;
use App\Models\Order;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Clients\ClientBuilder;
use Tests\Builders\Clients\EmailBuilder;
use Tests\Builders\Clients\TagBuilder;
use Tests\Builders\Divisions\DivisionBuilder;
use Tests\Builders\Orders\OrderBuilder;
use Tests\Builders\Users\UserBuilder;
use Tests\TestCase;

class ProfileSaveTest extends TestCase
{
    use DatabaseTransactions;

    protected DivisionBuilder $divisionBuilder;
    protected UserBuilder $userBuilder;
    protected OrderBuilder $orderBuilder;
    protected ClientBuilder $clientBuilder;
    protected TagBuilder $tagBuilder;
    protected EmailBuilder $emailBuilder;

    public function setUp(): void
    {
        $this->divisionBuilder = resolve(DivisionBuilder::class);
        $this->userBuilder = resolve(UserBuilder::class);
        $this->orderBuilder = resolve(OrderBuilder::class);
        $this->clientBuilder = resolve(ClientBuilder::class);
        $this->tagBuilder = resolve(TagBuilder::class);
        $this->emailBuilder = resolve(EmailBuilder::class);

        parent::setUp();
    }

    /** @test */
    public function success_update_name_and_check_audit_without_order()
    {
        $user = $this->loginUser();

        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $this->session(['division' => $division->toArray()]);

        /** @var $client Client */
        $client = $this->clientBuilder->create();

        $oldName = $client->name;
        $data = [
            'id' => $client->id,
            'name' => 'updated name',
            'lname' => $client->lname,
        ];

        $this->post(route('client.record.profile.save'), $data)
            ->assertJson([
                'success' => true,
                'msg' => "Client was updated",
                'record' => [
                    'id' => $client->id,
                    'name' => $data['name'],
                ]
            ])
        ;

        $client->refresh();

        /** @var $audit Audit */
        $audit = $client
            ->audits
            ->where('event', 'updated')
            ->first();

        $this->assertEquals($audit->user_id, $user->id);
        $this->assertEquals(0, $audit->is_client_activity);
        $this->assertEquals($audit->old_values['name'], $oldName);
        $this->assertEquals($audit->new_values['name'], $data['name']);
        $this->assertNull($audit->order_id);
    }

    /** @test */
    public function success_update_lname_and_check_audit_with_order()
    {
        $user = $this->loginUser();

        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $this->session(['division' => $division->toArray()]);

        /** @var $order Order */
        $order = $this->orderBuilder->create();

        /** @var $client Client */
        $client = $this->clientBuilder->create();

        $oldLName = $client->lname;
        $data = [
            'id' => $client->id,
            'name' => $client->name,
            'lname' => 'updated lname',
            'order_id' => $order->id,
        ];

        $this->post(route('client.record.profile.save'), $data)
            ->assertJson([
                'success' => true,
                'msg' => "Client was updated",
                'record' => [
                    'id' => $client->id,
                    'lname' => $data['lname'],
                ]
            ])
        ;

        $client->refresh();

        /** @var $audit Audit */
        $audit = $client
            ->audits
            ->where('event', 'updated')
            ->first();

        $this->assertEquals($audit->user_id, $user->id);
        $this->assertEquals(0, $audit->is_client_activity);
        $this->assertEquals($audit->old_values['lname'], $oldLName);
        $this->assertEquals($audit->new_values['lname'], $data['lname']);
        $this->assertEquals($audit->order_id, $order->id);
    }

    /** @test */
    public function success_update_email_is_primary()
    {
        $this->loginUser();

        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $this->session(['division' => $division->toArray()]);

        /** @var $client Client */
        $client = $this->clientBuilder->create();
        /** @var $email Client\Email */
        $email = $this->emailBuilder
            ->is_primary(0)
            ->client($client)
            ->create();

        $oldLName = $client->lname;
        $data = [
            'id' => $client->id,
            'name' => $client->name,
            'lname' => $client->lname,
            'emails' => [
                [
                    'client_id' => $client->id,
                    'id' => $email->id,
                    'value' => $email->value,
                    'is_primary' => 1,
                ]
            ],
        ];

        $this->assertEquals(0, $client->emails[0]->is_primary);

        $this->post(route('client.record.profile.save'), $data)
            ->assertJson([
                'success' => true,
                'msg' => "Client was updated",
                'record' => [
                    'id' => $client->id,
                    'emails' => [
                        [
                            'id' => $email->id,
                            'is_primary' => 1,
                        ]
                    ]
                ]
            ])
        ;
    }

    /** @test */
    public function add_tags_and_check_audit()
    {
        $this->loginUser();

        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $this->session(['division' => $division->toArray()]);

        /** @var $order Order */
        $order = $this->orderBuilder->create();

        /** @var $client Client */
        $client = $this->clientBuilder->create();

        $tag_1 = $this->tagBuilder->create();

        $data = [
            'id' => $client->id,
            'name' => $client->name,
            'lname' => 'updated lname',
            'order_id' => $order->id,
            'selectedTags' => [
                [
                    'key' => $tag_1->id,
                    'value' => $tag_1->title,
                ]
            ],
        ];

        $this->assertEmpty($client->tags);

        $this->post(route('client.record.profile.save'), $data)
            ->assertJson([
                'success' => true,
                'msg' => "Client was updated",
                'record' => [
                    'id' => $client->id,
                ]
            ])
        ;

        $client->refresh();

        $this->assertCount(1, $client->tags);

        $audit = Audit::query()
            ->where('auditable_type', Client::MORPH_NAME)
            ->where('auditable_id', $client->id)
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

        $tag_1 = $this->tagBuilder->create();
        $tag_2 = $this->tagBuilder->create();

        /** @var $order Order */
        $order = $this->orderBuilder->create();

        /** @var $client Client */
        $client = $this->clientBuilder->tags($tag_1)->create();

        $data = [
            'id' => $client->id,
            'name' => $client->name,
            'lname' => 'updated lname',
            'order_id' => $order->id,
            'selectedTags' => [
                [
                    'key' => $tag_1->id,
                    'value' => $tag_1->title,
                ],
                [
                    'key' => $tag_2->id,
                    'value' => $tag_2->title,
                ]
            ],
        ];

        $this->assertCount(1, $client->tags);

        $this->post(route('client.record.profile.save'), $data)
            ->assertJson([
                'success' => true,
                'msg' => "Client was updated",
                'record' => [
                    'id' => $client->id,
                ]
            ])
        ;

        $client->refresh();

        $this->assertCount(2, $client->tags);

        $audit = Audit::query()
            ->where('auditable_type', Client::MORPH_NAME)
            ->where('auditable_id', $client->id)
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

        /** @var $tag_1 Client\Tag */
        $tag_1 = $this->tagBuilder->create();

        /** @var $order Order */
        $order = $this->orderBuilder->create();

        /** @var $client Client */
        $client = $this->clientBuilder->tags($tag_1)->create();

        $data = [
            'id' => $client->id,
            'name' => $client->name,
            'lname' => 'updated lname',
            'order_id' => $order->id,
            'selectedTags' => [],
        ];

        $this->assertCount(1, $client->tags);

        $this->post(route('client.record.profile.save'), $data)
            ->assertJson([
                'success' => true,
                'msg' => "Client was updated",
                'record' => [
                    'id' => $client->id,
                ]
            ])
        ;

        $client->refresh();

        $this->assertCount(0, $client->tags);

        $audit = Audit::query()
            ->where('auditable_type', Client::MORPH_NAME)
            ->where('auditable_id', $client->id)
            ->where('event', Audit::EVENT_SYNC)
            ->first();

        $this->assertEquals($audit->old_values['custom_tags'], [$tag_1->title]);
        $this->assertEquals(0, count($audit->new_values['custom_tags']));
    }

}

