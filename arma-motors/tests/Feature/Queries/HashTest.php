<?php

namespace Tests\Feature\Queries;

use App\Models\Hash;
use App\Types\Permissions;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature\Mutations\Catalog\Car\BrandToggleActiveTest;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;

class HashTest extends TestCase
{
    use DatabaseTransactions;
    use AdminBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
    }

    /** @test */
    public function success()
    {
        $hashModel = Hash::query()->where('alias', Hash::ALIAS_BRAND)->first();

        $this->assertNull($hashModel);

        $response = $this->graphQL($this->getQueryStr(Hash::ALIAS_BRAND))->assertOk();

        $responseData = $response->json('data.hashData');

        $this->assertArrayHasKey('status', $responseData);
        $this->assertArrayHasKey('hash', $responseData);

        $this->assertTrue($responseData['status']);
        $this->assertNotNull($responseData['hash']);

        $hashModel = Hash::query()->where('alias', Hash::ALIAS_BRAND)->first();

        $firstHash = $responseData['hash'];
        $this->assertNotNull($hashModel);
        $this->assertEquals($firstHash, $hashModel->hash);

        $newResponse = $this->graphQL($this->getQueryStr(Hash::ALIAS_BRAND));

        $this->assertEquals($firstHash, $newResponse->json('data.hashData.hash'));
    }

    /** @test */
    public function change_hash()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Permissions::CATALOG_BRAND_EDIT)
            ->create();
        $this->loginAsAdmin($admin);

        $response = $this->graphQL($this->getQueryStr(Hash::ALIAS_BRAND))->assertOk();
        $firstHash = $response->json('data.hashData.hash');

        // переключаем активность бренду
        $this->graphQL(BrandToggleActiveTest::getQueryStr(1));

        $newResponse = $this->graphQL($this->getQueryStr(Hash::ALIAS_BRAND))->assertOk();
        $changeHash = $newResponse->json('data.hashData.hash');

        $this->assertNotEquals($firstHash, $changeHash);
    }

    /** @test */
    public function get_hash_model()
    {
        $hashModel = Hash::query()->where('alias', Hash::ALIAS_MODEL)->first();
        $this->assertNull($hashModel);

        $response = $this->graphQL($this->getQueryStr(Hash::ALIAS_MODEL))->assertOk();
        $this->assertIsString($response->json('data.hashData.hash'));

        $hashModel = Hash::query()->where('alias', Hash::ALIAS_MODEL)->first();
        $this->assertNotNull($hashModel);
    }

    /** @test */
    public function get_hash_service()
    {
//        $hashModel = Hash::query()->where('alias', Hash::ALIAS_SERVICE)->first();
//
//        $this->assertNull($hashModel);

        $response = $this->graphQL($this->getQueryStr(Hash::ALIAS_SERVICE))->assertOk();
        $this->assertIsString($response->json('data.hashData.hash'));

        $hashModel = Hash::query()->where('alias', Hash::ALIAS_SERVICE)->first();
        $this->assertNotNull($hashModel);
    }

    /** @test */
    public function get_hash_dealership()
    {
//        $hashModel = Hash::query()->where('alias', Hash::ALIAS_DEALERSHIP)->first();
//        $this->assertNull($hashModel);

        $response = $this->graphQL($this->getQueryStr(Hash::ALIAS_DEALERSHIP))->assertOk();

        $this->assertIsString($response->json('data.hashData.hash'));

        $hashModel = Hash::query()->where('alias', Hash::ALIAS_DEALERSHIP)->first();
        $this->assertNotNull($hashModel);
    }

    public static function getQueryStr($alias): string
    {
        return "{hashData (alias: {$alias}) {status, hash}}";
    }
}



