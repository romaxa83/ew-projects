<?php

namespace Tests\Feature\Http\Api\OneC\Orders\Categories;

use App\Models\Orders\Categories\OrderCategory;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class OrderCategoryControllerTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public function test_list(): void
    {
        $this->loginAsModerator();

        OrderCategory::factory()
            ->times(3)
            ->create();

        $this->getJson(route('1c.orderParts.index'))
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        $this->getJsonStructure()
                    ],
                ]
            );
    }

    protected function getJsonStructure(): array
    {
        return [
            'id',
            'guid',
            'active',
            'need_description'
        ];
    }

    public function test_store(): void
    {
        $this->loginAsModerator();

        $this->postJson(
            route('1c.orderParts.store'),
            $this->getData()
        )
            ->assertCreated()
            ->assertJsonStructure(
                [
                    'data' => $this->getJsonStructure(),
                ]
            );
    }

    protected function getData(): array
    {
        return [
            'guid' => $this->faker->uuid,
            'active' => true,
            'translations' => [
                [
                    'language' => 'en',
                    'title' => 'en title',
                    'description' => 'en description',
                ],
                [
                    'language' => 'es',
                    'title' => 'es title',
                    'description' => 'es description',
                ]
            ],
        ];
    }

    public function test_update(): void
    {
        $this->loginAsModerator();

        $orderPart = OrderCategory::factory()->create();

        $data = $this->getData();
        unset($data['guid']);

        $this->putJson(
            route('1c.orderParts.update', $orderPart->guid),
            $data
        )
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => $this->getJsonStructure(),
                ]
            );
    }

    public function test_destroy(): void
    {
        $this->loginAsModerator();

        $orderPart = OrderCategory::factory()->create();

        $this->deleteJson(route('1c.orderParts.destroy', $orderPart->guid))
            ->assertOk();
    }

    public function test_update_guid(): void
    {
        $this->loginAsModerator();

        $orderPart = OrderCategory::factory()->create();

        $data = [
            [
                'id' => $orderPart->id,
                'guid' => $newGuid = $this->faker->uuid,
            ]
        ];

        $oldGuid = $orderPart->guid;

        $this->assertDatabaseHas(OrderCategory::TABLE, ['guid' => $oldGuid]);
        $this->assertDatabaseMissing(OrderCategory::TABLE, ['guid' => $newGuid]);

        $this->postJson(route('1c.orderParts.update.guid'), compact('data'))
            ->assertOk();

        $this->assertDatabaseHas(OrderCategory::TABLE, ['guid' => $newGuid]);
        $this->assertDatabaseMissing(OrderCategory::TABLE, ['guid' => $oldGuid]);
    }
}
