<?php

namespace Tests\Unit\Listeners;

use App\Events\ChangeHashEvent;
use App\Listeners\ChangeHashListeners;
use App\Models\Hash;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ChangeHashListenersTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function brand_hash()
    {
        $hashBrandModel = Hash::query()->where('alias', Hash::ALIAS_BRAND)->first();
        $this->assertNull($hashBrandModel);

        $event = new ChangeHashEvent(Hash::ALIAS_BRAND);
        $listener = new ChangeHashListeners();
        $listener->handle($event);

        $hashBrandModel = Hash::query()->where('alias', Hash::ALIAS_BRAND)->first();
        $this->assertNotNull($hashBrandModel);
    }

    /** @test */
    public function model_hash()
    {
        $hashBrandModel = Hash::query()->where('alias', Hash::ALIAS_MODEL)->first();
        $this->assertNull($hashBrandModel);

        $event = new ChangeHashEvent(Hash::ALIAS_MODEL);
        $listener = new ChangeHashListeners();
        $listener->handle($event);

        $hashBrandModel = Hash::query()->where('alias', Hash::ALIAS_MODEL)->first();
        $this->assertNotNull($hashBrandModel);
    }
}

