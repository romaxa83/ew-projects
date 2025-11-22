<?php

namespace Tests\Feature\Queries\FrontOffice\Orders\Dealer;

use App\Enums\Catalog\Products\ProductOwnerType;
use App\GraphQL\Queries\FrontOffice\Orders\Dealer as DealerProduct;
use App\Models\Catalog\Products\Product;
use App\Models\Companies\Company;
use App\Models\Dealers\Dealer;
use App\Models\Orders\Dealer\Order;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Builders\Catalog\ProductBuilder;
use Tests\Builders\Company\CompanyBuilder;
use Tests\Builders\Company\CompanyPriceBuilder;
use Tests\Builders\Dealers\DealerBuilder;
use Tests\Builders\Orders\Dealer\ItemBuilder;
use Tests\Builders\Orders\Dealer\OrderBuilder;
use Tests\TestCase;

class ProductsQueryTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = DealerProduct\ProductsQuery::NAME;

    protected ProductBuilder $productBuilder;
    protected DealerBuilder $dealerBuilder;
    protected OrderBuilder $orderBuilder;
    protected ItemBuilder $itemBuilder;
    protected CompanyBuilder $companyBuilder;
    protected CompanyPriceBuilder $companyPriceBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->productBuilder = resolve(ProductBuilder::class);
        $this->companyBuilder = resolve(CompanyBuilder::class);
        $this->companyPriceBuilder = resolve(CompanyPriceBuilder::class);
        $this->orderBuilder = resolve(OrderBuilder::class);
        $this->itemBuilder = resolve(ItemBuilder::class);
        $this->dealerBuilder = resolve(DealerBuilder::class);
    }

    /** @test */
    public function list_product(): void
    {
        Storage::fake('public');
        /** @var $order Order */
        $order = $this->orderBuilder->create();
        /** @var $company Company */
        $company = $this->companyBuilder->create();
        /** @var $dealer Dealer */
        $dealer = $this->dealerBuilder->setCompany($company)->create();

        $media_1 = UploadedFile::fake()->image('product1.jpg');
        $media_2 = UploadedFile::fake()->image('product2.pdf');

        $product_1_acces_1 = $this->productBuilder->create();
        $product_1_acces_2 = $this->productBuilder->create();
        $product_1_acces_3 = $this->productBuilder->create();

        $product_1 = $this->productBuilder->setRelations(
            $product_1_acces_1, $product_1_acces_2, $product_1_acces_3
        )->create();
        $product_2 = $this->productBuilder->create();

        $media_olmo_1 = "https://api.olmo.wezom.agency/storage/23/3164377765.jpg";
        $media_olmo_2 = "https://api.olmo.wezom.agency/storage/25/3164377765.jpg";
        $product_3 = $this->productBuilder->setOwnerType(ProductOwnerType::OLMO)
            ->setMedia($media_olmo_1, $media_olmo_2)
            ->create();

        $product_4 = $this->productBuilder->setActive(false)->create();
        $product_5 = $this->productBuilder->create();

        $this->itemBuilder->setOrder($order)->setProduct($product_1)->create();

        $product_1->addMedia($media_1)->toMediaCollection(Product::MEDIA_COLLECTION_NAME);
        $product_3->addMedia($media_2)->toMediaCollection(Product::MEDIA_COLLECTION_NAME);

        $price_1 = $this->companyPriceBuilder->setProduct($product_1)
            ->setCompany($company)->create();
        $price_2 = $this->companyPriceBuilder->setProduct($product_2)
            ->setCompany($company)->create();
        $price_3 = $this->companyPriceBuilder->setProduct($product_3)
            ->setCompany($company)->setDesc(null)->create();
        $price_4 = $this->companyPriceBuilder->setProduct($product_1_acces_1)
            ->setCompany($company)->create();
        $price_5 = $this->companyPriceBuilder->setProduct($product_1_acces_2)
            ->setCompany($company)->create();
        $price_6 = $this->companyPriceBuilder->setProduct($product_4)
            ->setCompany($company)->create();

        $this->loginAsDealerWithRole($dealer);

        $this->postGraphQL([
            'query' => $this->getQueryStr($order->id)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        [
                            'id' => $product_1_acces_1->id,
                            'price' => $price_4->price,
                            'accessories' => []
                        ],
                        [
                            'id' => $product_1_acces_2->id,
                            'price' => $price_5->price,
                            'accessories' => []
                        ],
                        [
                            'id' => $product_1->id,
                            'title' => $product_1->title,
                            'slug' => $product_1->slug,
                            'price' => $price_1->price,
                            'price_description' => $price_1->desc,
                            'owner_type' => ProductOwnerType::COOPER,
                            'category_id' => $product_1->category_id,
                            'img' => $product_1->getImgUrl(),
                            'is_added' => true,
                            'accessories' => [
                                [
                                    'id' => $product_1_acces_2->id,
                                    'price' => $price_5->price,
                                    'accessories' => []
                                ],
                                [
                                    'id' => $product_1_acces_1->id,
                                    'price' => $price_4->price,
                                    'accessories' => []
                                ],
                            ]
                        ],
                        [
                            'id' => $product_2->id,
                            'title' => $product_2->title,
                            'slug' => $product_2->slug,
                            'price' => $price_2->price,
                            'price_description' => $price_2->desc,
                            'owner_type' => ProductOwnerType::COOPER,
                            'category_id' => $product_2->category_id,
                            'img' => null,
                            'is_added' => false,
                            'accessories' => []
                        ],
                        [
                            'id' => $product_3->id,
                            'title' => $product_3->title,
                            'slug' => $product_3->slug,
                            'price' => $price_3->price,
                            'price_description' => null,
                            'owner_type' => ProductOwnerType::OLMO,
                            'category_id' => $product_3->category_id,
                            'img' => $media_olmo_1,
                            'is_added' => false,
                            'accessories' => []
                        ]
                    ]
                ]
            ])
            ->assertJsonCount(5, 'data.'. self::MUTATION)
            ->assertJsonCount(0, 'data.'. self::MUTATION.'.0.accessories')
            ->assertJsonCount(0, 'data.'. self::MUTATION.'.1.accessories')
            ->assertJsonCount(2, 'data.'. self::MUTATION.'.2.accessories')
            ->assertJsonCount(0, 'data.'. self::MUTATION.'.3.accessories')
            ->assertJsonCount(0, 'data.'. self::MUTATION.'.4.accessories')
        ;
    }

    /** @test */
    public function list_product_without_order_id(): void
    {
        Storage::fake('public');
        /** @var $order Order */
        $order = $this->orderBuilder->create();
        /** @var $company Company */
        $company = $this->companyBuilder->create();
        /** @var $dealer Dealer */
        $dealer = $this->dealerBuilder->setCompany($company)->create();

        $product_1 = $this->productBuilder->create();
        $product_2 = $this->productBuilder->create();
        $product_3 = $this->productBuilder->create();
        $product_4 = $this->productBuilder->create();

        $this->itemBuilder->setOrder($order)->setProduct($product_1)->create();

        $price_1 = $this->companyPriceBuilder->setProduct($product_1)
            ->setCompany($company)->create();
        $price_2 = $this->companyPriceBuilder->setProduct($product_2)
            ->setCompany($company)->create();
        $price_3 = $this->companyPriceBuilder->setProduct($product_3)
            ->setCompany($company)->create();

        $this->loginAsDealerWithRole($dealer);

        $this->postGraphQL([
            'query' => $this->getQueryStrWithoutOrder()
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        [
                            'id' => $product_1->id,
                            'is_added' => false
                        ],
                        [
                            'id' => $product_2->id,
                            'is_added' => false
                        ],
                        [
                            'id' => $product_3->id,
                            'is_added' => false
                        ]
                    ]
                ]
            ])
            ->assertJsonCount(3, 'data.'. self::MUTATION)
        ;
    }

    /** @test */
    public function list_empty_not_price(): void
    {
        /** @var $order Order */
        $order = $this->orderBuilder->create();
        /** @var $company Company */
        $company = $this->companyBuilder->create();
        /** @var $dealer Dealer */
        $dealer = $this->dealerBuilder->setCompany($company)->create();

        $product_1 = $this->productBuilder->create();
        $product_2 = $this->productBuilder->create();
        $product_3 = $this->productBuilder->create();
        $product_4 = $this->productBuilder->create();

        $price_1 = $this->companyPriceBuilder->setProduct($product_1)
            ->setCompany($company)->create();
        $price_2 = $this->companyPriceBuilder->setProduct($product_2)
            ->setCompany($company)->create();
        $price_3 = $this->companyPriceBuilder->setProduct($product_3)
            ->setCompany($company)->create();

        $this->loginAsDealerWithRole();

        $this->postGraphQL([
            'query' => $this->getQueryStr($order->id)
        ])
            ->assertJsonCount(0, 'data.'. self::MUTATION)
        ;
    }

    protected function getQueryStr($orderId): string
    {
        return sprintf(
            '
            {
                %s (order_id: %s) {
                    id
                    title
                    slug
                    price
                    price_description
                    owner_type
                    category_id
                    brand
                    img
                    is_added
                    accessories {
                        id
                        price
                        accessories {
                            id
                        }
                    }
                }
            }',
            self::MUTATION,
            $orderId
        );
    }

    protected function getQueryStrWithoutOrder(): string
    {
        return sprintf(
            '
            {
                %s{
                    id
                    is_added
                }
            }',
            self::MUTATION
        );
    }

    /** @test */
    public function not_auth(): void
    {
        /** @var $order Order */
        $order = $this->orderBuilder->create();

        $this->postGraphQL([
            'query' => $this->getQueryStr($order->id)
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

        $this->postGraphQL([
            'query' => $this->getQueryStr($order->id)
        ])
            ->assertJson([
                'errors' => [
                    ['message' => "No permission"]
                ]
            ])
        ;
    }
}

