<?php

namespace Tests\Feature\Mutations\FrontOffice\Orders\Dealer;

use App\Enums\Orders\Dealer\OrderStatus;
use App\GraphQL\Mutations\FrontOffice\Orders\Dealer\MediaDeleteMutation;
use App\Models\Dealers\Dealer;
use App\Models\Orders\Dealer\Order;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Tests\Builders\Dealers\DealerBuilder;
use Tests\Builders\Orders\Dealer\OrderBuilder;
use Tests\TestCase;
use Tests\Traits\Storage\TestStorage;

class MediaDeleteMutationTest extends TestCase
{
    use DatabaseTransactions;
    use TestStorage;

    public const MUTATION = MediaDeleteMutation::NAME;

    protected OrderBuilder $orderBuilder;
    protected DealerBuilder $dealerBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->orderBuilder = resolve(OrderBuilder::class);
        $this->dealerBuilder = resolve(DealerBuilder::class);
    }

    /** @test */
    public function success_delete(): void
    {
        $this->fakeMediaStorage();
        $dealer = $this->loginAsDealerWithRole();

        /** @var $order Order */
        $order = $this->orderBuilder->setDealer($dealer)->create();
        $order->addMedia(
            UploadedFile::fake()->image('category1.png')
        )
            ->toMediaCollection(Order::MEDIA_COLLECTION_NAME);

        $order->refresh();

        $this->postGraphQL([
            'query' => $this->getQueryStrDelete($order->media->first()->id)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'id' => $order->id
                    ]
                ]
            ])
        ;

        $order->refresh();
        $this->assertEmpty($order->media);
    }

    /** @test */
    public function fail_order_is_not_draft(): void
    {
        $this->fakeMediaStorage();
        $dealer = $this->loginAsDealerWithRole();

        /** @var $order Order */
        $order = $this->orderBuilder->setStatus(OrderStatus::CANCELED)
            ->setDealer($dealer)->create();
        $order->addMedia(
            UploadedFile::fake()->image('category1.png')
        )
            ->toMediaCollection(Order::MEDIA_COLLECTION_NAME);

        $order->refresh();

        $this->postGraphQL([
            'query' => $this->getQueryStrDelete($order->media->first()->id)
        ])
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
        $order->addMedia(
            UploadedFile::fake()->image('category1.png')
        )
            ->toMediaCollection(Order::MEDIA_COLLECTION_NAME);

        $order->refresh();

        $this->postGraphQL([
            'query' => $this->getQueryStrDelete($order->media->first()->id)
        ])
            ->assertJson([
                'errors' => [
                    ['message' => __("exceptions.dealer.order.can't this order")]
                ]
            ])
        ;
    }

    /** @test */
    public function fail_delete_for_main_dealer(): void
    {
        /** @var $dealer Dealer */
        $dealer = $this->dealerBuilder->setMain()->setData([
            'is_main_company' =>false
        ])->create();
        $this->loginAsDealerWithRole($dealer);
        $this->fakeMediaStorage();

        /** @var $order Order */
        $order = $this->orderBuilder->create();
        $order->addMedia(
            UploadedFile::fake()->image('category1.png')
        )
            ->toMediaCollection(Order::MEDIA_COLLECTION_NAME);

        $order->refresh();

        $this->postGraphQL([
            'query' => $this->getQueryStrDelete($order->media->first()->id)
        ])
            ->assertJson([
                'errors' => [
                    ['message' => __("exceptions.dealer.not_action_for_main")]
                ]
            ])
        ;

        $order->refresh();
        $this->assertNotEmpty($order->media);
    }

    protected function getQueryStrDelete($id): string
    {
        return sprintf(
            '
            mutation {
                %s (
                    id: %s
                ) {
                    id
                }
            }',
            self::MUTATION,
            $id,
        );
    }
}

