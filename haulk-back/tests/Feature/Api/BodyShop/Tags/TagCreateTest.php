<?php

namespace Api\BodyShop\Tags;

use App\Models\Tags\Tag;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\TestCase;

class TagCreateTest extends TestCase
{
    use DatabaseTransactions;

    public function test_it_forbidden_to_users_create_for_not_authorized_users()
    {
        $this->postJson(route('body-shop.tags.store'), [])->assertUnauthorized();
    }

    public function test_it_create()
    {
        $this->loginAsBodyShopAdmin();

        $formRequest = [
            'name' => 'Name Test',
            'color' => '#ffffff',
            'type' => Tag::TYPE_VEHICLE_OWNER,
        ];

        $this->assertDatabaseMissing(Tag::TABLE_NAME, $formRequest);

        $this->postJson(route('body-shop.tags.store'), $formRequest)
            ->assertCreated();

        $this->assertDatabaseHas(Tag::TABLE_NAME, $formRequest);
    }

    public function test_it_validation_messages()
    {
        $this->loginAsBodyShopSuperAdmin();

        $this->postJson(route('body-shop.tags.store'), [])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJson(
                [
                    'errors' => [
                        [
                            'source' => ['parameter' => 'name'],
                            'title' => 'The Name field is required.',
                            'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                        ],
                        [
                            'source' => ['parameter' => 'color'],
                            'title' => 'The Color field is required.',
                            'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                        ],
                        [
                            'source' => ['parameter' => 'type'],
                            'title' => 'The Type field is required.',
                            'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                        ],
                    ],
                ]
            );
    }

    public function test_max_count_exception()
    {
        Tag::factory()->times(10)
            ->create(['type' => Tag::TYPE_VEHICLE_OWNER, 'carrier_id' => 1]);

        $this->loginAsBodyShopSuperAdmin();

        // test if tags from other company
        $formRequest = [
            'name' => 'Name Test2',
            'color' => '#ffffff',
            'type' => Tag::TYPE_VEHICLE_OWNER,
        ];
        $this->postJson(route('body-shop.tags.store'), $formRequest)
            ->assertCreated();
        $this->assertDatabaseHas(Tag::TABLE_NAME, $formRequest);

        // test if tags from other type
        Tag::factory()->times(10)
            ->create(['type' => 'other_type', 'carrier_id' => null]);
        $formRequest = [
            'name' => 'Name Test',
            'color' => '#ffffff',
            'type' => Tag::TYPE_VEHICLE_OWNER,
        ];
        $this->postJson(route('body-shop.tags.store'), $formRequest)
            ->assertCreated();
        $this->assertDatabaseHas(Tag::TABLE_NAME, $formRequest);


        // test max tags count of current company and current type
        Tag::factory()->times(10)
            ->create(['type' => Tag::TYPE_VEHICLE_OWNER, 'carrier_id'=> null]);
        $formRequest = [
            'name' => 'Name Test3',
            'color' => '#ffffff',
            'type' => Tag::TYPE_VEHICLE_OWNER,
        ];
        $this->postJson(route('body-shop.tags.store'), $formRequest)
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertDatabaseMissing(Tag::TABLE_NAME, $formRequest);
    }
}
