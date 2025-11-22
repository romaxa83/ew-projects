<?php

namespace Tests\Feature\Mutations\FrontOffice\Orders\Dealer;

use App\Enums\Orders\Dealer\OrderStatus;
use App\GraphQL\Mutations\FrontOffice\Orders\Dealer\MediaUploadMutation;
use App\Models\Dealers\Dealer;
use App\Models\Orders\Dealer\Order;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Tests\Builders\Dealers\DealerBuilder;
use Tests\Builders\Orders\Dealer\OrderBuilder;
use Tests\TestCase;
use Tests\Traits\Storage\TestStorage;

class MediaUploadMutationTest extends TestCase
{
    use DatabaseTransactions;
    use TestStorage;

    public const MUTATION = MediaUploadMutation::NAME;

    protected OrderBuilder $orderBuilder;
    protected DealerBuilder $dealerBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->orderBuilder = resolve(OrderBuilder::class);
        $this->dealerBuilder = resolve(DealerBuilder::class);
    }

    /** @test */
    public function success_upload(): void
    {
        $this->fakeMediaStorage();
        $dealer = $this->loginAsDealerWithRole();
        /** @var $order Order */
        $order = $this->orderBuilder->setDealer($dealer)->create();

        $data = [
            'id' => $order->id,
        ];

        $file_1 = UploadedFile::fake()->image('test.pdf');
        $file_2 = UploadedFile::fake()->image('test.pdf');

        $attributes = [
            'operations' => sprintf(
                '{"query": "mutation ($media: [Upload!]!) {%s (id: \"%s\", media: $media) {id}}"}',
                self::MUTATION,
                data_get($data, 'id'),
            ),
            'map' => '{ "media": ["variables.media"] }',
            'media' => [$file_1, $file_2],
        ];

//        $attributes = [
//            'operations' => sprintf(
//                '{"query": "mutation ($media: Upload!) {%s (id: \"%s\", media: $media) {id}}"}',
//                self::MUTATION,
//                data_get($data, 'id'),
//            ),
//            'map' => '{ "media": ["variables.media"] }',
//            'media' => $file_1,
//        ];

        $this->assertEmpty($order->media);

        $this->postGraphQlUpload($attributes)
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        "id" => $order->id
                    ]
                ]
            ])
        ;

        $order->refresh();

        $this->assertCount(2, $order->media);
    }

    /** @test */
    public function success_add_new_image(): void
    {
        $this->fakeMediaStorage();

        $dealer = $this->loginAsDealerWithRole();

        /** @var $order Order */
        $order = $this->orderBuilder->setDealer($dealer)->create();

        $order->addMedia(
            UploadedFile::fake()->image('test1.pdf')
        )
            ->toMediaCollection(Order::MEDIA_COLLECTION_NAME);

        $data = [
            'id' => $order->id,
        ];

        $file = UploadedFile::fake()->image('test.pdf');

        $attributes = [
            'operations' => sprintf(
                '{"query": "mutation ($media: [Upload!]!) {%s (id: \"%s\", media: $media) {id}}"}',
                self::MUTATION,
                data_get($data, 'id'),
            ),
            'map' => '{ "media": ["variables.media"] }',
            'media' => [$file],
        ];

        $order->refresh();
        $this->assertCount(1, $order->media);

        $this->postGraphQlUpload($attributes)
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        "id" => $order->id
                    ]
                ]
            ])
        ;

        $order->refresh();

        $this->assertCount(2, $order->media);
    }

    /** @test */
    public function fail_order_is_not_draft(): void
    {
        $this->fakeMediaStorage();
        $dealer = $this->loginAsDealerWithRole();

        /** @var $order Order */
        $order = $this->orderBuilder->setStatus(OrderStatus::SENT)
            ->setDealer($dealer)->create();

        $data = [
            'id' => $order->id,
        ];

        $file = UploadedFile::fake()->image('test.pdf');

        $attributes = [
            'operations' => sprintf(
                '{"query": "mutation ($media: [Upload!]!) {%s (id: \"%s\", media: $media) {id}}"}',
                self::MUTATION,
                data_get($data, 'id'),
            ),
            'map' => '{ "media": ["variables.media"] }',
            'media' => [$file],
        ];

        $this->assertEmpty($order->media);

        $this->postGraphQlUpload($attributes)
            ->assertJson([
                'errors' => [
                    ['message' => __("exceptions.dealer.order.order is not draft")]
                ]
            ])
        ;
    }

    /** @test */
    public function fail_dealer_not_owner(): void
    {
        $this->fakeMediaStorage();
        $this->loginAsDealerWithRole();

        /** @var $order Order */
        $order = $this->orderBuilder->create();

        $data = [
            'id' => $order->id,
        ];

        $file = UploadedFile::fake()->image('test.pdf');

        $attributes = [
            'operations' => sprintf(
                '{"query": "mutation ($media: [Upload!]!) {%s (id: \"%s\", media: $media) {id}}"}',
                self::MUTATION,
                data_get($data, 'id'),
            ),
            'map' => '{ "media": ["variables.media"] }',
            'media' => [$file],
        ];

        $this->assertEmpty($order->media);

        $this->postGraphQlUpload($attributes)
            ->assertJson([
                'errors' => [
                    ['message' => __("exceptions.dealer.order.can't this order")]
                ]
            ])
        ;
    }

    /** @test */
    public function fail_upload_for_main_dealer(): void
    {
        /** @var $dealer Dealer */
        $dealer = $this->dealerBuilder->setMain()->setData([
            'is_main_company' =>false
        ])->create();
        $this->loginAsDealerWithRole($dealer);

        $this->fakeMediaStorage();

        /** @var $order Order */
        $order = $this->orderBuilder->create();

        $data = [
            'id' => $order->id,
        ];

        $file = UploadedFile::fake()->image('test.pdf');

        $attributes = [
            'operations' => sprintf(
                '{"query": "mutation ($media: [Upload!]!) {%s (id: \"%s\", media: $media) {id}}"}',
                self::MUTATION,
                data_get($data, 'id'),
            ),
            'map' => '{ "media": ["variables.media"] }',
            'media' => [$file],
        ];

        $this->assertEmpty($order->media);

        $this->postGraphQlUpload($attributes)
            ->assertJson([
                'errors' => [
                    ['message' => __("exceptions.dealer.not_action_for_main")]
                ]
            ])
        ;

        $order->refresh();

        $this->assertEmpty($order->media);
    }
}
