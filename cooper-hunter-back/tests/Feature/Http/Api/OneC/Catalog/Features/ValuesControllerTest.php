<?php

namespace Tests\Feature\Http\Api\OneC\Catalog\Features;

use App\Models\Catalog\Features\Feature;
use App\Models\Catalog\Features\Value;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ValuesControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function test_create(): void
    {
        $this->loginAsModerator();

        $this->postJson(route('1c.values.store'), $this->data())
            ->assertCreated();
    }

    protected function data(): array
    {
        return [
            'feature_guid' => Feature::factory()->create()->guid,
            'active' => true,
            'title' => 'title',
        ];
    }

    public function test_update(): void
    {
        $this->loginAsModerator();

        $value = Value::factory()->create();

        $this->putJson(route('1c.values.update', $value->id), $this->data())
            ->assertOk();
    }

    public function test_delete(): void
    {
        $this->loginAsModerator();

        $value = Value::factory()->create();

        $this->deleteJson(route('1c.values.destroy', $value->id))
            ->assertOk();

        $this->assertModelMissing($value);
    }
}
