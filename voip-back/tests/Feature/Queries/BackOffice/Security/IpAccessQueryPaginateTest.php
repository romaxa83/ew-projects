<?php

namespace Tests\Feature\Queries\BackOffice\Security;

use App\Models\Security\IpAccess;

class IpAccessQueryPaginateTest extends BaseIpAccessQueryTest
{
    public function test_it_get_correct_items_when_arguments_has_per_page(): void
    {
        IpAccess::factory()->count(15)->create();

        $this->loginAsIpAccessManager();

        $perPage = 5;
        $query = sprintf(
            'query { %s (per_page: %s) { data { id address description } } }',
            self::QUERY,
            $perPage
        );

        ['data' => $items] = $this->postGraphQLBackOffice(compact('query'))
            ->assertOk()
            ->json('data.' . self::QUERY);

        self::assertCount(5, $items);
    }

    public function test_it_get_correct_page(): void
    {
        $this->loginAsIpAccessManager();

        IpAccess::factory()->count(9)->create();

        $page = 2;
        $perPage = 5;
        $query = sprintf(
            'query { %s (page: %s, per_page: %s) { data { id address description } } }',
            self::QUERY,
            $page,
            $perPage
        );

        ['data' => $items] = $this->postGraphQLBackOffice(compact('query'))
            ->assertOk()
            ->json('data.' . self::QUERY);

        self::assertCount(4, $items);
    }

    public function test_get_correct_items_when_search_by_address(): void
    {
        $this->loginAsIpAccessManager();

        IpAccess::factory()->create(['address' => '192.168.0.1']);
        IpAccess::factory()->create(['address' => '192.168.1.1']);
        IpAccess::factory()->create(['address' => '192.168.0.100']);
        IpAccess::factory()->create(['address' => '192.168.0.254']);

        $queryString = '192.168.0';
        $query = sprintf(
            'query { %s (query: "%s") { data { id address description } } }',
            self::QUERY,
            $queryString
        );

        ['data' => $items] = $this->postGraphQLBackOffice(compact('query'))
            ->assertOk()
            ->json('data.' . self::QUERY);

        self::assertCount(3, $items);
    }

    public function test_it_get_correct_items_when_search_by_description(): void
    {
        $this->loginAsIpAccessManager();

        IpAccess::factory()->create(['description' => 'Description 1']);
        IpAccess::factory()->create(['description' => 'Description 2']);
        IpAccess::factory()->create(['description' => 'Description 3']);
        IpAccess::factory()->create(['description' => 'some other text 1']);
        IpAccess::factory()->create(['description' => 'some other text 2']);

        $queryString = 'ption';
        $query = sprintf(
            'query { %s (query: "%s") { data { id address description } } }',
            self::QUERY,
            $queryString
        );

        ['data' => $items] = $this->postGraphQLBackOffice(compact('query'))
            ->assertOk()
            ->json('data.' . self::QUERY);

        self::assertCount(3, $items);
    }

    public function test_order_by_name(): void
    {
        $this->loginAsIpAccessManager();

        $address1 = '192.168.0.1';
        $address2 = '192.168.0.2';
        $address3 = '192.168.0.30';
        $address4 = '192.168.0.200';
        $address5 = '192.168.1.200';
        IpAccess::factory()->create(['address' => $address1]);
        IpAccess::factory()->create(['address' => $address2]);
        IpAccess::factory()->create(['address' => $address3]);
        IpAccess::factory()->create(['address' => $address4]);
        IpAccess::factory()->create(['address' => $address5]);

        $query = sprintf(
            'query { %s (sort: "%s") { data { id address description } } }',
            self::QUERY,
            'address-desc'
        );

        ['data' => $items] = $this->postGraphQLBackOffice(compact('query'))
            ->assertOk()
            ->json('data.' . self::QUERY);

        $items = collect($items);

        self::assertEquals($address5, $items->shift()['address']);
        self::assertEquals($address4, $items->shift()['address']);
        self::assertEquals($address3, $items->shift()['address']);
        self::assertEquals($address2, $items->shift()['address']);
        self::assertEquals($address1, $items->shift()['address']);
    }

    public function test_order_by_description(): void
    {
        $this->loginAsIpAccessManager();

        $description1 = 'B text';
        $description2 = 'A text';
        $description3 = 'C text';
        $description4 = 'E text';
        $description5 = 'D text';
        IpAccess::factory()->create(['description' => $description1]);
        IpAccess::factory()->create(['description' => $description2]);
        IpAccess::factory()->create(['description' => $description3]);
        IpAccess::factory()->create(['description' => $description4]);
        IpAccess::factory()->create(['description' => $description5]);

        $query = sprintf(
            'query { %s (sort: "%s") { data { id address description } } }',
            self::QUERY,
            'description-asc'
        );

        ['data' => $items] = $this->postGraphQLBackOffice(compact('query'))
            ->assertOk()
            ->json('data.' . self::QUERY);

        $items = collect($items);

        self::assertEquals($description2, $items->shift()['description']);
        self::assertEquals($description1, $items->shift()['description']);
        self::assertEquals($description3, $items->shift()['description']);
        self::assertEquals($description5, $items->shift()['description']);
        self::assertEquals($description4, $items->shift()['description']);
    }

    public function test_order_by_active(): void
    {
        $this->loginAsIpAccessManager();

        $access1 = IpAccess::factory()->create(['active' => true]);
        $access2 = IpAccess::factory()->create(['active' => true]);
        $access3 = IpAccess::factory()->create(['active' => false]);
        $access4 = IpAccess::factory()->create(['active' => false]);

        $query = sprintf(
            'query { %s (sort: "%s") { data { id address description } } }',
            self::QUERY,
            'active-asc'
        );

        ['data' => $items] = $this->postGraphQLBackOffice(compact('query'))
            ->assertOk()
            ->json('data.' . self::QUERY);

        $items = collect($items);

        self::assertEquals($access3->id, $items->shift()['id']);
        self::assertEquals($access4->id, $items->shift()['id']);
        self::assertEquals($access1->id, $items->shift()['id']);
        self::assertEquals($access2->id, $items->shift()['id']);
    }
}
