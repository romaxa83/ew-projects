<?php

namespace Tests\Feature\Mutations\FrontOffice\Orders\Dealer;

use App\Events\Orders\Dealer\UpdatePackingSlipEvent;
use App\GraphQL\Mutations\FrontOffice\Orders\Dealer\UpdatePackingSlipMutation;
use App\Listeners\Orders\Dealer\SendDataToOnecListener;
use App\Models\Media\Media;
use App\Models\Orders\Dealer\Order;
use App\Models\Orders\Dealer\PackingSlip;
use App\Services\OneC\Client\RequestClient;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Mockery\MockInterface;
use Tests\Builders\Dealers\DealerBuilder;
use Tests\Builders\Orders\Dealer\OrderBuilder;
use Tests\Builders\Orders\Dealer\PackingSlipBuilder;
use Tests\TestCase;
use Tests\Traits\Storage\TestStorage;

class UpdatePackingSlipMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;
    use TestStorage;

    public const MUTATION = UpdatePackingSlipMutation::NAME;

    protected OrderBuilder $orderBuilder;
    protected PackingSlipBuilder $packingSlipBuilder;
    protected DealerBuilder $dealerBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->orderBuilder = resolve(OrderBuilder::class);
        $this->packingSlipBuilder = resolve(PackingSlipBuilder::class);
        $this->dealerBuilder = resolve(DealerBuilder::class);
    }

    /** @test */
    public function success_update(): void
    {
        Event::fake([UpdatePackingSlipEvent::class]);

        $dealer = $this->loginAsDealerWithRole();

        /** @var $order Order */
        $order = $this->orderBuilder->setDealer($dealer)->create();
        /** @var $packing_slip PackingSlip */
        $packing_slip = $this->packingSlipBuilder->setOrder($order)
            ->setData([
                'tracking_number' => null,
                'tracking_company' => null,
            ])->create();

        $data = $this->data();
        $data['id'] = $packing_slip->id;

        $this->assertNotEquals($packing_slip->tracking_number, data_get($data, 'packing_slip.tracking_number'));
        $this->assertNotEquals($packing_slip->tracking_company, data_get($data, 'packing_slip.tracking_company'));

        $res = [
            "success" => true
        ];

        $this->mock(RequestClient::class, function(MockInterface $mock) use($res) {
            $mock->shouldReceive("postRequest")
                ->andReturn($res);
        });

        $this->postGraphQL([
            'query' => $this->getQueryStr($data)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'id' => $packing_slip->id,
                        'tracking_number' => data_get($data, 'packing_slip.tracking_number'),
                        'tracking_company' => data_get($data, 'packing_slip.tracking_company'),
                    ]
                ]
            ])
        ;

//        $packing_slip->refresh();
//        $this->assertEquals($packing_slip->tracking_number, data_get($data, 'packing_slip.tracking_number'));
//        $this->assertEquals($packing_slip->tracking_company, data_get($data, 'packing_slip.tracking_company'));
    }

    protected function getQueryStr(array $data): string
    {
        return sprintf(
            '
            mutation {
                %s (
                    id: %s
                    packing_slip: {
                        tracking_number: "%s"
                        tracking_company: "%s"
                    }
                ) {
                    id
                    tracking_number
                    tracking_company
                }
            }',
            self::MUTATION,
            data_get($data, 'id'),
            data_get($data, 'packing_slip.tracking_number'),
            data_get($data, 'packing_slip.tracking_company')
        );
    }

    /** @test */
    public function success_update_with_media(): void
    {
        $this->fakeMediaStorage();
        $dealer = $this->loginAsDealerWithRole();

        /** @var $order Order */
        $order = $this->orderBuilder->setDealer($dealer)->create();
        /** @var $packing_slip PackingSlip */
        $packing_slip = $this->packingSlipBuilder->setOrder($order)
            ->setData([
                'tracking_number' => null,
                'tracking_company' => null,
            ])->create();

        $file_1 = UploadedFile::fake()->image('file_1.jpg');
        $file_2 = UploadedFile::fake()->image('file_2.pdf');

        $data = $this->data();
        $data['id'] = $packing_slip->id;

        $attributes = [
            'operations' => sprintf(
                '{"query": "mutation ($media: [Upload!]) {%s (id: \"%s\", packing_slip: {tracking_number: \"%s\", tracking_company: \"%s\"}, media: $media) {id, media{url}}}"}',
                self::MUTATION,
                data_get($data, 'id'),
                data_get($data, 'packing_slip.tracking_number'),
                data_get($data, 'packing_slip.tracking_company'),
            ),
            'map' => '{ "media": ["variables.media"] }',
            'media' => [$file_1, $file_2],
        ];

        $this->assertEmpty($packing_slip->media);
        $this->assertNotEquals($packing_slip->tracking_number, data_get($data, 'packing_slip.tracking_number'));
        $this->assertNotEquals($packing_slip->tracking_company, data_get($data, 'packing_slip.tracking_company'));

        $res = [
            "success" => true
        ];

        $this->mock(RequestClient::class, function(MockInterface $mock) use($res) {
            $mock->shouldReceive("postRequest")
                ->andReturn($res);
        });

        $id = $this->postGraphQlUpload($attributes)
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'id' => $packing_slip->id
                    ]
                ]
            ])
            ->assertJsonCount(2, 'data.'.self::MUTATION.'.media')
            ->json('data.'.self::MUTATION.'.id')
        ;

        $this->assertDatabaseCount(Media::TABLE, 2);
        $this->assertDatabaseHas(Media::TABLE, ['model_type' => PackingSlip::MORPH_NAME, 'model_id' => $id]);

        $packing_slip->refresh();

        $this->assertNotEmpty($packing_slip->media);
    }

    /** @test */
    public function fail_update_with_media(): void
    {
        $this->fakeMediaStorage();
        $dealer = $this->loginAsDealerWithRole();

        /** @var $order Order */
        $order = $this->orderBuilder->setDealer($dealer)->create();
        /** @var $packing_slip PackingSlip */
        $packing_slip = $this->packingSlipBuilder->setOrder($order)
            ->setData([
                'tracking_number' => 'tracking_number_1',
                'tracking_company' => 'tracking_company_1',
            ])->create();

        $file_1 = UploadedFile::fake()->image('file_1.jpg');
        $file_2 = UploadedFile::fake()->image('file_2.pdf');

        $data = $this->data();
        $data['id'] = $packing_slip->id;

        $attributes = [
            'operations' => sprintf(
                '{"query": "mutation ($media: [Upload!]) {%s (id: \"%s\", packing_slip: {tracking_number: \"%s\", tracking_company: \"%s\"}, media: $media) {id, media{url}}}"}',
                self::MUTATION,
                data_get($data, 'id'),
                data_get($data, 'packing_slip.tracking_number'),
                data_get($data, 'packing_slip.tracking_company'),
            ),
            'map' => '{ "media": ["variables.media"] }',
            'media' => [$file_1, $file_2],
        ];

        $this->assertEmpty($packing_slip->media);
        $this->assertNotEquals($packing_slip->tracking_number, data_get($data, 'packing_slip.tracking_number'));
        $this->assertNotEquals($packing_slip->tracking_company, data_get($data, 'packing_slip.tracking_company'));

        $res = [
            "success" => false
        ];

        $this->mock(RequestClient::class, function(MockInterface $mock) use($res) {
            $mock->shouldReceive("postRequest")
                ->andReturn($res);
        });

        $this->postGraphQlUpload($attributes)
            ->assertJson([
                'errors' => [
                    [
                        'message' => "validation",
                        'extensions' => [
                            'validation' => [
                                "media" => [__('exceptions.dealer.order.packing_slip.not update by onec')]
                            ]
                        ],
                    ]
                ]
            ])
        ;

        $oldPackingSlip = $packing_slip;

        $packing_slip->refresh();

        $this->assertEmpty($packing_slip->media);
        $this->assertEquals($packing_slip->tracking_company, $oldPackingSlip->tracking_company);
        $this->assertEquals($packing_slip->tracking_number, $oldPackingSlip->tracking_number);
    }

    /** @test */
    public function fail_dealer_not_owner(): void
    {
        $this->loginAsDealerWithRole();

        /** @var $order Order */
        $order = $this->orderBuilder->create();
        /** @var $packing_slip PackingSlip */
        $packing_slip = $this->packingSlipBuilder->setOrder($order)
            ->setData([
                'tracking_number' => null,
                'tracking_company' => null,
            ])->create();

        $data = $this->data();
        $data['id'] = $packing_slip->id;

        $this->postGraphQL([
            'query' => $this->getQueryStr($data)
        ])
            ->assertJson([
                'errors' => [
                    [
                        'message' => "validation",
                        'extensions' => [
                            'validation' => [
                                "media" => [__("exceptions.dealer.order.can't this order")]
                            ]
                        ],
                    ]
                ]
            ])
        ;
    }

    /** @test */
    public function fail_update_for_main_dealer(): void
    {
        $dealer = $this->dealerBuilder->setMain()->setData([
            'is_main_company' =>false
        ])->create();
        $this->loginAsDealerWithRole($dealer);

        /** @var $order Order */
        $order = $this->orderBuilder->create();

        /** @var $packing_slip PackingSlip */
        $packing_slip = $this->packingSlipBuilder->setOrder($order)
            ->setData([
                'tracking_number' => null,
                'tracking_company' => null,
            ])->create();

        $data = $this->data();
        $data['id'] = $packing_slip->id;

        $this->postGraphQL([
            'query' => $this->getQueryStr($data)
        ])
            ->assertJson([
                'errors' => [
                    [
                        'message' => "validation",
                        'extensions' => [
                            'validation' => [
                                "media" => [__("exceptions.dealer.not_action_for_main")]
                            ]
                        ],
                    ]
                ]
            ])
        ;
    }

    /** @test */
    public function not_auth(): void
    {
        /** @var $order Order */
        $order = $this->orderBuilder->create();

        /** @var $packing_slip PackingSlip */
        $packing_slip = $this->packingSlipBuilder->setOrder($order)
            ->setData([
                'tracking_number' => null,
                'tracking_company' => null,
            ])->create();

        $data = $this->data();
        $data['id'] = $packing_slip->id;

        $this->postGraphQL([
            'query' => $this->getQueryStr($data)
        ])
            ->assertJson([
                'errors' => [
                    ['message' => "Unauthorized"]
                ]
            ])
        ;
    }

    /** @test */
    public function not_perm(): void
    {
        $this->loginAsDealer();

        /** @var $order Order */
        $order = $this->orderBuilder->create();
        /** @var $packing_slip PackingSlip */
        $packing_slip = $this->packingSlipBuilder->setOrder($order)
            ->setData([
                'tracking_number' => null,
                'tracking_company' => null,
            ])->create();

        $data = $this->data();
        $data['id'] = $packing_slip->id;

        $this->postGraphQL([
            'query' => $this->getQueryStr($data)
        ])
            ->assertJson([
                'errors' => [
                    ['message' => "No permission"]
                ]
            ])
        ;
    }

    public function data(): array
    {
        return [
            'packing_slip' => [
                'tracking_number' => $this->faker->postcode,
                'tracking_company' => $this->faker->company,
            ]
        ];
    }
}

