<?php

namespace Feature\Http\Api\V1\Inventories\Inventory\Catalog;

use App\Enums\Inventories\InventoryPackageType;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class PackageTypeTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function success_list()
    {
        $this->loginUserAsSuperAdmin();

        $this->getJson(route('api.v1.inventories.package-types'))
            ->assertJson([
                'data' => [
                    ['key' => InventoryPackageType::Custom->value, 'title' => InventoryPackageType::Custom->label()],
                    ['key' => InventoryPackageType::Carrier->value, 'title' => InventoryPackageType::Carrier->label()],
                ],
            ])
            ->assertJsonCount(count(InventoryPackageType::cases()), 'data')
        ;
    }

    /** @test */
    public function not_auth()
    {
        $res = $this->getJson(route('api.v1.inventories.package-types'));

        self::assertUnauthenticatedMessage($res);
    }
}
