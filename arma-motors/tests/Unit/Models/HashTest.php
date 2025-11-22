<?php

namespace Tests\Unit\Models;

use App\Models\Hash;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class HashTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function hash_array_data()
    {
        $data = [
            'some' => 'some data'
        ];

        $hash = Hash::hash($data);

        $this->assertNotNull($data);
        $this->assertIsString($hash);
    }

    /** @test */
    public function hash_array_empty()
    {
        $data = [];

        $hash = Hash::hash($data);

        $this->assertNotNull($data);
        $this->assertIsString($hash);
    }

    /** @doesNotPerformAssertions */
    public function test_success_asset_alias()
    {
        Hash::assetAlias(Hash::ALIAS_MODEL);
    }

    /** @test */
    public function fail_asset_alias()
    {
        $this->expectException(\InvalidArgumentException::class);

        Hash::assetAlias('wrong_alias');
    }

    /** @test */
    public function get_hash()
    {
        $hashModel = Hash::query()->where('alias', Hash::ALIAS_BRAND)->first();

        $this->assertNull($hashModel);

        $hash = app(Hash::class)->getHash(Hash::ALIAS_BRAND);

        $this->assertNotNull($hash);
        $this->assertIsString($hash);

        $hashModel = Hash::query()->where('alias', Hash::ALIAS_BRAND)->first();

        $this->assertNotNull($hashModel);
        $this->assertEquals($hashModel->hash, $hash);
    }
}

