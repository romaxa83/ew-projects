<?php

namespace Tests\Feature\Queries\FrontOffice\Utilities;

use App\GraphQL\Queries\FrontOffice\Utilities\ZipCodeByIpAddressQuery;
use App\Models\Locations\IpRange;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ZipCodeByIpAddressQueryTest extends TestCase
{
    use DatabaseTransactions;

    public function test_get_zip_by_ip(): void
    {
        $range = IpRange::factory()->create();

        $ip = '127.0.0.1';

        $this->postGraphQL(
            GraphQLQuery::query(ZipCodeByIpAddressQuery::NAME)
                ->make(),
            [
                'REMOTE_ADDR' => $ip
            ]
        )
            ->assertJson(
                [
                    'data' => [
                        ZipCodeByIpAddressQuery::NAME => $range->zip
                    ]
                ]
            );
    }
}