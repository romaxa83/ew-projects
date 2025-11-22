<?php

namespace Tests\Feature\Http\Api\OneC\Technicians;

use App\Models\Locations\Country;
use App\Models\Locations\State;
use App\Models\OneC\Moderator;
use App\Models\Technicians\Technician;
use App\Permissions\Catalog\Categories\DeletePermission;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\Permissions\RoleHelperTrait;

class TechniciansControllerTest extends TestCase
{
    use DatabaseTransactions;
    use RoleHelperTrait;

    public function test_unauthorized(): void
    {
        $this->getJson(route('1c.technicians.index'))
            ->assertUnauthorized();
    }

    public function test_no_permission(): void
    {
        $role = $this->generateRole(
            'Wrong permission role',
            [DeletePermission::KEY],
            Moderator::GUARD
        );

        $this->loginAsModerator(role: $role);

        $this->getJson(route('1c.technicians.index'))
            ->assertForbidden();
    }

    public function test_index(): void
    {
        $this->loginAsModerator();

        $countOfRecords = 5;

        Technician::factory()
            ->count($countOfRecords)
            ->create();

        $response = $this->getJson(route('1c.technicians.index'))
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        [
                            'id',
                            'email',
                            'phone',
                            'guid',
                        ],
                    ],
                ],
            );

        $data = $response->json('data');
        $this->assertCount($countOfRecords, $data);
    }

    public function test_new(): void
    {
        $this->loginAsModerator();

        $countOfRecords = 3;

        Technician::factory()
            ->count($countOfRecords)
            ->create(['guid' => null]);

        $response = $this->getJson(route('1c.technicians.new'))
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        [
                            'id',
                            'email',
                            'phone',
                            'guid',
                        ],
                    ],
                ],
            );

        $data = $response->json('data');
        $this->assertCount($countOfRecords, $data);
    }

    public function test_show(): void
    {
        $this->loginAsModerator();

        $technician = Technician::factory()
            ->create();

        $this->getJson(route('1c.technicians.show', $technician->guid))
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        'id',
                        'email',
                        'phone',
                        'guid'
                    ]
                ]
            );
    }

    public function test_show_not_found(): void
    {
        $this->loginAsModerator();

        $this->getJson(route('1c.technicians.show', 0))
            ->assertNotFound();
    }

//    public function test_store(): void
//    {
//        $this->loginAsModerator();
//
//        $this->postJson(
//            route('1c.technicians.store'),
//            $this->getParams() + ['state_id' => State::factory()->create()->id, 'license' => 'license']
//        )
//            ->assertCreated()
//            ->assertJsonStructure(
//                $this->getJsonStructure()
//            );
//    }

    protected function getParams(): array
    {
        return [
            'first_name' => 'First',
            'last_name' => 'Last',
            'password' => 'password1',
            'password_confirmation' => 'password1',
            'email' => 'test@gmail.com',
            'phone' => null,
        ];
    }

    protected function getJsonStructure(): array
    {
        return [
            'data' => [
                'id',
                'email',
                'phone',
                'guid',
            ],
        ];
    }

//    public function test_update(): void
//    {
//        $this->loginAsModerator();
//
//        $technician = Technician::factory()->create();
//
//        $this->putJson(
//            route('1c.technicians.update', $technician->guid),
//            [
//                'first_name' => 'First',
//                'last_name' => 'Last',
//                'email' => (string)$technician->email,
//                'state_id' => State::factory()->create()->id,
//                'license' => 'license'
//            ]
//        )
//            ->assertJsonStructure(
//                $this->getJsonStructure()
//            );
//    }

    public function test_destroy(): void
    {
        $this->loginAsModerator();

        $technician = Technician::factory()->create();

        $this->deleteJson(route('1c.technicians.destroy', $technician->guid))
            ->assertOk();
    }

    public function test_import(): void
    {
        $this->loginAsModerator();

        $this->postJson(
            route('1c.technicians.import'),
            [
                'technicians' => [
                    array_merge($this->getParams(), [
                        'phone' => '+380970000000',
                        'state_id' => State::factory()->create()->id,
                        'country_code' => Country::factory()->create()->country_code,
                        'license' => 'license'
                    ]),
                ]
            ]
        )
            ->assertJsonStructure(
                [
                    'data' => [
                        [
                            'id',
                            'email',
                            'phone',
                            'guid'
                        ]
                    ],
                ]
            );
    }
}
