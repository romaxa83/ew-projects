<?php

namespace WezomCms\Catalog;

use Event;
use Illuminate\Console\Command;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Routing\Router;
use Route;
use SidebarMenu;
use WezomCms\Catalog\Database\Seeders\ColorSpecificationsSeeder;
use WezomCms\Catalog\Helpers\GatesHelpers;
use WezomCms\Catalog\Jobs\DisappearsProductFromSale;
use WezomCms\Catalog\Models\Category;
use WezomCms\Catalog\Models\Product;
use WezomCms\Catalog\Models\Specifications\Specification;
use WezomCms\Core\BaseServiceProvider;
use WezomCms\Core\Contracts\Assets\AssetManagerInterface;
use WezomCms\Core\Contracts\PermissionsContainerInterface;
use WezomCms\Core\Contracts\SitemapXmlGeneratorInterface;
use WezomCms\Core\Foundation\Helpers;
use WezomCms\Core\Models\Administrator;

class CatalogServiceProvider extends BaseServiceProvider
{
    /**
     * All module widgets.
     *
     * @var array|string|null
     */
    protected $widgets = 'cms.catalog.widgets.widgets';

    /**
     * Dashboard widgets.
     *
     * @var array|string|null
     */
    protected $dashboard = 'cms.catalog.widgets.dashboards';

    /**
     * Custom translation keys.
     *
     * @var array
     */
    protected $translationKeys = [
        'cms-catalog::admin.products.pieces',
        'cms-catalog::site.products.pieces',
        'cms-catalog::site.products.sort.Default',
        'cms-catalog::site.products.sort.Cost asc',
        'cms-catalog::site.products.sort.Cost desc',
        'cms-catalog::site.products.sort.Created at',
        'cms-catalog::admin.Dimensions (сm)',
    ];


    /**
     * Application booting.
     */
    public function boot()
    {
        Router::macro('filter', function ($name, $uri, $action, array $wheres = []) {
            Route::get("{$uri}/filter/{filter}", $action)
                ->name("{$name}.filter")
                ->where(array_merge(['filter' => '[a-z0-9;=,:_\-\.]+'], $wheres));

            Route::get($uri, $action)->where($wheres)->name($name);
        });

        parent::boot();
    }

    /**
     * @param  PermissionsContainerInterface  $permissions
     */
    public function permissions(PermissionsContainerInterface $permissions)
    {
        $permissions->add('categories', __('cms-catalog::admin.categories.Categories'))->withEditSettings();
        $permissions->add('collections', __('cms-catalog::admin.collection.collections'))->withEditSettings();
        $permissions->add('labels', __('cms-catalog::admin.labels.names'))->withEditSettings();

        $permissions->add(
            'products',
            __('cms-catalog::admin.products.Products'),
            [
                'view',
                'create',
                'edit' => GatesHelpers::modelGate('products.edit'),
                'delete' => GatesHelpers::modelGate('products.delete'),
                'restore' => GatesHelpers::modelGate('products.restore'),
                'force-delete' => GatesHelpers::modelGate('products.force-delete'),
                'edit-settings',
                'copy' => GatesHelpers::modelGate('products.copy'),
                'moderate' => GatesHelpers::productModerateGate(),
            ]
        );

        if (config('cms.catalog.brands.enabled')) {
            $permissions->add('brands', __('cms-catalog::admin.brands.Brands'))->withEditSettings();
        }

        if (config('cms.catalog.models.enabled')) {
            $permissions->add('models', __('cms-catalog::admin.models.Models'))->withEditSettings();
        }

        $permissions->add(
            'specifications',
            __('cms-catalog::admin.specifications.Specifications'),
            [
                'view',
                'create',
                'edit',
                'edit-settings',
                'delete' => function (Administrator $administrator, $specification) {
                    if (in_array($specification->type, [Specification::COLOR, Specification::SIZE], true)) {
                        return false;
                    }

                    return $administrator->hasAccess('specifications.delete');
                },
            ]
        );

        $permissions->add('catalog-seo-templates', __('cms-catalog::admin.catalog-seo-templates.SEO templates'))
            ->withEditSettings();

        $permissions->editSettings('search', __('cms-catalog::admin.search.Edit search settings'));
    }

    public function adminMenu()
    {
        // Catalog
        $catalog = SidebarMenu::get('catalog');
        if (!$catalog) {
            // Catalog
            $catalog = SidebarMenu::add(__('cms-catalog::admin.Catalog'))
                ->data('icon', 'fa-shopping-bag')
                ->data('position', 1)
                ->nickname('catalog');
        }

        // Categories
        $catalog->add(__('cms-catalog::admin.categories.Categories'), route('admin.categories.index'))
            ->data('permission', 'categories.view')
            ->data('icon', 'fa-object-group')
            ->data('position', 1);

        // Products
        $catalog->add(__('cms-catalog::admin.products.Products'), route('admin.products.index'))
            ->data('permission', 'products.view')
            ->data('icon', 'fa-list')
            ->data('position', 2);

        // Collection

        $catalog->add(__('cms-catalog::admin.collection.collections'), route('admin.collections.index'))
            ->data('permission', 'collections.view')
            ->data('icon', 'fa-list')
            ->data('position', 3);


        // Labels
        $catalog->add(__('cms-catalog::admin.labels.names'), route('admin.labels.index'))
            ->data('permission', 'labels.view')
            ->data('icon', 'fa-tags')
            ->data('position', 4);

        // Brands
//        if (config('cms.catalog.brands.enabled')) {
            $catalog->add(__('cms-catalog::admin.brands.Brands'), route('admin.brands.index'))
                ->data('permission', 'brands.view')
                ->data('icon', 'fa-ravelry')
                ->data('position', 3);
//        }

        // Models
//        if (config('cms.catalog.models.enabled')) {
//            $catalog->add(__('cms-catalog::admin.models.Models'), route('admin.models.index'))
//                ->data('permission', 'models.view')
//                ->data('icon', 'fa-dot-circle-o')
//                ->data('position', 4);
//        }

        // Specifications
        $catalog->add(__('cms-catalog::admin.specifications.Specifications'), route('admin.specifications.index'))
            ->data('permission', 'specifications.view')
            ->data('icon', 'fa-rocket')
            ->data('position', 5);

        // SEO templates
//        $catalog->add(
//            __('cms-catalog::admin.catalog-seo-templates.SEO templates'),
//            route('admin.catalog-seo-templates.index')
//        )
//            ->data('permission', 'catalog-seo-templates.view')
//            ->data('icon', 'fa-line-chart')
//            ->data('position', 6);

        // Search
//        $catalog->add(__('cms-catalog::admin.search.Search'), route('admin.search.settings'))
//            ->data('permission', 'search.edit-settings')
//            ->data('icon', 'fa-search')
//            ->data('position', 15)
//            ->nickname('search-settings');
    }

    protected function afterBootForAdminPanel()
    {
        app(AssetManagerInterface::class)
            ->addJs('vendor/cms/catalog/js/build.js')
            ->group(AssetManagerInterface::GROUP_ADMIN);
        app(AssetManagerInterface::class)
            ->addJs('vendor/cms/catalog/collection.js', )
            ->group(AssetManagerInterface::GROUP_ADMIN);
    }

    /**
     * Register module listeners.
     */
    protected function registerListeners()
    {
        parent::registerListeners();

        if ($this->app->runningInConsole()) {
            Event::listen('cms:install:after_migrate', function (Command $command) {
                if (
                    !Specification::where('type', Specification::COLOR)->exists()
                    && $command->confirm('Do your want run seed "Create color specification"', 'yes')
                ) {
                    $command->call(
                        'db:seed',
                        ['--class' => ColorSpecificationsSeeder::class, '--force' => (bool)$command->option('force')]
                    );
                }
            });

            Event::listen('cms:install', function (Command $command) {
                $command->call(
                    'vendor:publish',
                    ['--provider' => static::class, '--tag' => 'assets']
                );
            });
        }

        if (!$this->app['isBackend']) {
            Event::listen('live_search', function ($query) {
                $products = Product::published()
                    ->whereTranslationLike('name', '%' . Helpers::escapeLike($query) . '%')
                    ->limit(3)
                    ->latest('id')
                    ->get();

                $categories = Category::published()
                    ->whereTranslationLike('name', '%' . Helpers::escapeLike($query) . '%')
                    ->limit(3)
                    ->latest('id')
                    ->get();

                return $products->merge($categories);
            });
        }
    }

    /**
     * @param  Schedule  $schedule
     */
    /*public function jobs(Schedule $schedule)
    {
        $schedule->job(new DisappearsProductFromSale())->hourly();
    }*/

    /**
     * @return array
     */
    public function sitemap()
    {
        $result = [
            [
                'id' => 'catalog',
                'parent_id' => 0,
                'sort' => 0,
                'name' => settings('categories.site.name'),
                'url' => route('catalog'),
            ]
        ];

        return array_merge($result, Category::getForSiteMap());
    }

    /**
     * @param  SitemapXmlGeneratorInterface  $sitemap
     * @throws \ErrorException
     */
    public function sitemapXml(SitemapXmlGeneratorInterface $sitemap)
    {
        $sitemap->add(function () {
            return Category::published()
                ->get()
                ->mapWithKeys(function (Category $category) {
                    return [$category->id => $category->getFrontUrl()];
                });
        });

        $sitemap->add(function () {
            return Product::published()
                ->get()
                ->mapWithKeys(function (Product $product) {
                    return [$product->id => $product->getFrontUrl()];
                });
        });
    }
}
