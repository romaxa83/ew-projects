<?php

namespace WezomCms\ProductReviews;

use Event;
use SidebarMenu;
use WezomCms\Catalog\Helpers\GatesHelpers;
use WezomCms\Core\BaseServiceProvider;
use WezomCms\Core\Contracts\PermissionsContainerInterface;
use WezomCms\ProductReviews\Enums\Ratings;
use WezomCms\ProductReviews\Enums\SortVariants;
use WezomCms\ProductReviews\Models\ProductReview;
use WezomCms\ProductReviews\Observers\ProductReviewObserver;

class ProductReviewsServiceProvider extends BaseServiceProvider
{
    /**
     * All module widgets.
     *
     * @var array|string|null
     */
    protected $widgets = 'cms.product-reviews.product-reviews.widgets';

    /**
     * Dashboard widgets.
     *
     * @var array|string|null
     */
    protected $dashboard = 'cms.product-reviews.product-reviews.dashboards';

    /**
     * Custom translation keys.
     *
     * @var array
     */
    protected $translationKeys = [
        'cms-product-reviews::site.Rating',
    ];

    /**
     * List of enum classes for auto scanning localization keys.
     *
     * @var array
     */
    protected $enumClasses = [
        Ratings::class,
        SortVariants::class,
    ];

    /**
     * Application booting.
     */
    public function boot()
    {
        ProductReview::observe(ProductReviewObserver::class);

        parent::boot();
    }

    /**
     * @param  PermissionsContainerInterface  $permissions
     */
    public function permissions(PermissionsContainerInterface $permissions)
    {
        $permissions->add(
            'product-reviews',
            __('cms-product-reviews::admin.Product reviews'),
            [
                'view',
                'create',
                'edit' => GatesHelpers::modelGate('product-reviews.edit'),
                'delete' => GatesHelpers::modelGate('product-reviews.delete'),
            ]
        )->withEditSettings();
    }

    protected function registerListeners()
    {
        parent::registerListeners();

        if (!$this->app['isBackend']) {
            Event::listen('catalog.sort_variants', function () {
                return require $this->root('config/products-sort-variants.php');
            });
        }
    }

    public function adminMenu()
    {
        // Catalog
        $catalog = SidebarMenu::get('catalog');
        if (!$catalog) {
            $catalog = SidebarMenu::add(__('cms-catalog::admin.Catalog'))
                ->data('icon', 'fa-shopping-bag')
                ->data('position', 1)
                ->nickname('catalog');
        }

        $count = ProductReview::where('published', false)->count();

        $catalog->data('badge', $catalog->data('badge') + $count)
            ->data('badge_type', 'warning');

        $catalog->add(__('cms-product-reviews::admin.Product reviews'), route('admin.product-reviews.index'))
            ->data('permission', 'product-reviews.view')
            ->data('icon', 'fa-comments')
            ->data('badge', $count)
            ->data('badge_type', 'warning')
            ->data('position', 7);
    }
}
