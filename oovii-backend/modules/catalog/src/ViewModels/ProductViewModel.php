<?php

namespace WezomCms\Catalog\ViewModels;

use Artesaos\SEOTools\Traits\SEOTools;
use Illuminate\Database\Eloquent\Collection;
use Spatie\SchemaOrg\ItemAvailability;
use Spatie\SchemaOrg\Schema;
use Spatie\ViewModels\ViewModel;
use WezomCms\Catalog\Contracts\ReviewRatingInterface;
use WezomCms\Catalog\Filter\Factory\UrlBuilderFactory;
use WezomCms\Catalog\Filter\Handlers\BrandHandler;
use WezomCms\Catalog\Models\Brand;
use WezomCms\Catalog\Models\Category;
use WezomCms\Catalog\Models\Model;
use WezomCms\Catalog\Models\Product;
use WezomCms\Catalog\Models\ProductImage;
use WezomCms\Catalog\Models\Specifications\SpecValue;
use WezomCms\Core\Contracts\Assets\AssetManagerInterface;
use WezomCms\Core\Foundation\Helpers;
use WezomCms\Core\Traits\BreadcrumbsTrait;
use WezomCms\Core\Traits\LangSwitchingGenerator;
use WezomCms\Core\Traits\MicroDataTrait;
use WezomCms\Core\Traits\RecursiveBreadcrumbsTrait;
use WezomCms\ProductReviews\ProductReviewsServiceProvider;

class ProductViewModel extends ViewModel
{
    use BreadcrumbsTrait;
    use LangSwitchingGenerator;
    use MicroDataTrait;
    use RecursiveBreadcrumbsTrait;
    use SEOTools;

    /**
     * @var Product
     */
    public $product;

    /**
     * @var Category|null
     */
    public $category;

    /**
     * @var Brand|null
     */
    public $brand;

    /**
     * @var bool
     */
    public $reviewsEnabled;

    /**
     * ProductViewModel constructor.
     * @param  Product  $product
     */
    public function __construct(Product $product)
    {
        $this->product = $product;

        $this->category = $this->product->category;

        $this->brand = config('cms.catalog.brands.enabled') ? $this->product->brand()->published()->first() : null;

        $this->setLangSwitchers($product, 'catalog.product', ['slug' => 'slug', 'id' => 'model.id']);

        $this->setSeo();

        $this->setBreadcrumbs();

        $this->setOpenGraph();

        $this->setMicroData();

        $this->reviewsEnabled = Helpers::providerLoaded(ProductReviewsServiceProvider::class);
    }

    /**
     * @return \Illuminate\Support\Collection|Product[]
     */
    public function variations()
    {
        return $this->product->variations;
    }

    /**
     * @return int
     */
    public function countReviews(): int
    {
        return $this->reviewsEnabled ? $this->product->publishedReviews()->count() : 0;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function specifications(): \Illuminate\Support\Collection
    {
        $result = collect();

        if (config('cms.catalog.brands.enabled')) {
            $brand = $this->product->brand()->published()->first();
            if ($brand) {
                $result->push($brand);
            }
        }

        if (config('cms.catalog.models.enabled')) {
            $model = $this->product->model()->published()->first();
            if ($model) {
                $result->push($model);
            }
        }

        $result = $result->merge($this->product->publishedSpecifications()->get()->sortBy('specification.sort'));

        return $result->map(function ($item) {
            if ($item instanceof Brand) {
                return [
                    'name' => __('cms-catalog::site.products.Brand'),
                    'value' => $item->name
                ];
            } elseif ($item instanceof Model) {
                return [
                    'name' => __('cms-catalog::site.products.Model'),
                    'value' => $item->name
                ];
            } elseif ($item instanceof SpecValue) {
                return [
                    'image' => $item->specification->getExistImageUrl(),
                    'name' => $item->specification->name,
                    'value' => $item->name
                ];
            } else {
                return $item;
            }
        });
    }

    /**
     * @return Collection|ProductImage[]|string[]
     */
    public function gallery()
    {
        return $this->product->gallery;
    }

    private function setSeo()
    {
        [$title, $h1, $description, $keywords] = $this->parseTemplate();

        $this->seo()
            ->setTitle($this->product->title ?: $title)
            ->setH1($this->product->h1 ?: $h1)
            ->setPageName($this->product->name)
            ->setDescription($this->product->description ?: $description)
            ->metatags()
            ->setKeywords($this->product->keywords ?: $keywords);
    }

    /**
     * @return array
     */
    private function parseTemplate(): array
    {
        $settings = settings('product.products', []);

        $replace = [
            '[name]' => $this->product->name,
            '[id]' => $this->product->id,
            '[cost]' => money($this->product->cost, true),
        ];

        $replace['[brand]'] = $this->brand ? $this->brand->name : '';

        if (config('cms.catalog.models.enabled')) {
            $model = $this->product->model;
            $replace['[model]'] = $model ? $model->name : '';
        }
        $from = array_keys($replace);
        $to = array_values($replace);

        $title = str_replace($from, $to, array_get($settings, 'title'));
        $h1 = str_replace($from, $to, array_get($settings, 'h1'));
        $description = str_replace($from, $to, array_get($settings, 'description'));
        $keywords = str_replace($from, $to, array_get($settings, 'keywords'));

        return [$title, $h1, $description, $keywords];
    }

    private function setBreadcrumbs()
    {
        // Breadcrumbs
        $this->addBreadcrumb(
            settings('categories.site.name', __('cms-catalog::site.catalog.Catalog')),
            route('catalog')
        );

        if ($this->category) {
            $this->addRecursiveBreadcrumbs($this->category);
        }

        if ($this->brand && $this->category) {
            $this->addBreadcrumb(
                $this->brand->name,
                UrlBuilderFactory::category($this->category)->buildUrlWith(BrandHandler::NAME, $this->brand->slug)
            );
        }

        $this->addBreadcrumb($this->product->name, $this->product->getFrontUrl());
    }

    private function setOpenGraph()
    {
        $openGraph = $this->seo()->opengraph();

        $this->product->getExistImages('big')->each(function (ProductImage $image) use ($openGraph) {
            $openGraph->addImage($image->getImageUrl('big'), $image->getImageSize('big'));
        });
    }

    private function setMicroData()
    {
        $schemaProduct = Schema::product()
            ->name($this->product->name)
            ->sku($this->product->id)
            ->description($this->seo()->metatags()->getDescription());

        if ($this->product->category) {
            $schemaProduct->category($this->product->category->name);
        }

        if ($this->brand) {
            $schemaProduct->brand(Schema::brand()->name($this->brand->name));
        }

        if ($this->product->imageExists()) {
            $schemaProduct->image($this->product->getImageUrl());
        }

        $offer = Schema::offer()
            ->price($this->product->cost)
            ->url($this->product->getFrontUrl())
            ->availability($this->product->available ? ItemAvailability::InStock : ItemAvailability::OutOfStock)
            ->priceCurrency(money()->code());

        $schemaProduct->offers($offer);

        // Add reviews
        if ($this->reviewsEnabled && $reviews = $this->product->publishedReviews) {
            /** @var \Illuminate\Support\Collection $reviews */
            $schemaReviews = $reviews->map(function ($review) {
                $schemaReview = Schema::review()
                    ->text($review->text)
                    ->datePublished($review->created_at->format('Y-m-d'))
                    ->author(Schema::person()->name($review->name));

                if ($review instanceof ReviewRatingInterface) {
                    $schemaReview->reviewRating(Schema::rating()->ratingValue($review->getRating()));
                }

                return $schemaReview;
            });
            $schemaProduct->reviews($schemaReviews->toArray());

            // Set product average rating.
            $averageRating = $reviews->avg(function ($review) {
                return $review instanceof ReviewRatingInterface ? $review->getRating() : 0;
            });

            if ($averageRating !== null && $reviews->count()) {
                $schemaProduct->aggregateRating(
                    Schema::aggregateRating()->ratingValue($averageRating)->reviewCount($reviews->count())
                );
            }
        }

        $this->renderMicroData($schemaProduct);
    }
}
