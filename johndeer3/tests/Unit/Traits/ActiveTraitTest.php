<?php

namespace Tests\Unit\Traits;

use App\Models\Country;
use App\Models\User\Role;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ActiveTraitTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function success_is_active()
    {
        $model = Country::query()->first();

        $this->assertTrue($model->active);
        $this->assertTrue($model->isActive());

        $model->update(['active' => false]);

        $this->assertFalse($model->active);
        $this->assertFalse($model->isActive());
    }

    /** @test */
    public function fail_is_active_not_field()
    {
        $model = Role::query()->first();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Don't have \"active\" field in the model");

        $this->assertTrue($model->isActive());
    }

    /** @test */
    public function success_toggle_active()
    {
        $model = Country::query()->first();

        $this->assertTrue($model->active);

        $model->toggleActive();

        $this->assertFalse($model->active);
    }

    /** @test */
    public function fail_toggle_active_not_field()
    {
        $model = Role::query()->first();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Don't have \"active\" field in the model");

        $model->toggleActive();
    }

    /** @test */
    public function success_scope()
    {
        $model = Country::query()->active()->first();
        $modelID = $model->id;

        $model->update(['active' => false]);

        $model = Country::query()->active()->first();

        $this->assertNotEquals($model->id, $modelID);
    }

    /** @test */
    public function fail_scope_not_field()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Don't have \"active\" field in the model");

        Role::query()->active()->first();
    }
}

