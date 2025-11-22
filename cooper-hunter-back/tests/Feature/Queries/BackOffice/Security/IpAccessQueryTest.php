<?php

namespace Tests\Feature\Queries\BackOffice\Security;

use App\Models\Security\IpAccess;
use Illuminate\Testing\TestResponse;

class IpAccessQueryTest extends BaseIpAccessQueryTest
{
    public function test_not_permitted_admin_can_get_list_of_ips(): void
    {
        $this->loginAsAdmin();

        $this->test_guest_cant_get_list_of_ips();
    }

    public function test_guest_cant_get_list_of_ips(): void
    {
        $response = $this->query()
            ->assertOk();

        $this->assertGraphQlUnauthorized($response);
    }

    private function query(): TestResponse
    {
        $query = sprintf(
            'query { %s { data { id address description active } } }',
            self::QUERY
        );

        return $this->postGraphQLBackOffice(compact('query'));
    }

    public function test_permitted_admin_can_get_list_of_ips(): void
    {
        $this->loginAsIpAccessManager();

        IpAccess::factory()->count(10)->create();

        $response = $this->query()
            ->assertOk();

        $items = $response->json('data.'.self::QUERY.'.data');

        self::assertCount(
            10,
            $items
        );
    }

}
