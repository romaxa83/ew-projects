<?php

namespace App\Providers;

use App\Models\About\AboutCompany;
use App\Models\About\ForMemberPage;
use App\Models\Catalog\Categories\Category;
use App\Models\Catalog\Features\Specification;
use App\Models\Catalog\Manuals\Manual;
use App\Models\Catalog\Products\Product;
use App\Models\Catalog\Troubleshoots\Troubleshoot;
use App\Models\Companies\Company;
use App\Models\Content\OurCases\OurCase;
use App\Models\Content\OurCases\OurCaseCategory;
use App\Models\Dealers\Dealer;
use App\Models\News\News;
use App\Models\News\PhotoAlbum;
use App\Models\News\Video;
use App\Models\OneC\Moderator;
use App\Models\Orders\Dealer\PackingSlip;
use App\Models\Orders\Order;
use App\Models\Orders\Dealer\Order as DealerOrder;
use App\Models\Orders\OrderStatusHistory;
use App\Models\Projects\Project;
use App\Models\Projects\System;
use App\Models\Sliders\Slider;
use App\Models\Support\SupportRequest;
use App\Models\Technicians\Technician;
use App\Models\Warranty\WarrantyInfo\WarrantyInfo;
use App\Models\Warranty\WarrantyInfo\WarrantyInfoPackage;
use App\Services\Catalog\Categories\CategoryStorageService;
use Core\Chat\Models\Conversation;
use Core\Chat\Models\Message;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;
use Laravel\Telescope\Watchers\RequestWatcher;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(RequestWatcher::class, \App\Services\Telescope\Watchers\RequestWatcher::class);
        $this->app->singleton(CategoryStorageService::class);
    }

    public function boot(): void
    {
        $this->registerMorphMap();
    }

    public static function morphs(): array
    {
        return [
            Technician::MORPH_NAME => Technician::class,
            Dealer::MORPH_NAME => Dealer::class,
            Category::MORPH_NAME => Category::class,
            Product::MORPH_NAME => Product::class,
            Project::MORPH_NAME => Project::class,
            Manual::MORPH_NAME => Manual::class,
            WarrantyInfo::MORPH_NAME => WarrantyInfo::class,
            WarrantyInfoPackage::MORPH_NAME => WarrantyInfoPackage::class,
            Troubleshoot::MORPH_NAME => Troubleshoot::class,
            Moderator::MORPH_NAME => Moderator::class,
            AboutCompany::MORPH_NAME => AboutCompany::class,
            ForMemberPage::MORPH_NAME => ForMemberPage::class,
            OurCaseCategory::MORPH_NAME => OurCaseCategory::class,
            OurCase::MORPH_NAME => OurCase::class,
            OrderStatusHistory::MORPH_NAME => OrderStatusHistory::class,
            News::MORPH_NAME => News::class,
            Video::MORPH_NAME => Video::class,
            PhotoAlbum::MORPH_NAME => PhotoAlbum::class,
            Slider::MORPH_NAME => Slider::class,
            Specification::MORPH_NAME => Specification::class,
            Order::MORPH_NAME => Order::class,
            DealerOrder::MORPH_NAME => DealerOrder::class,
            SupportRequest::MORPH_NAME => SupportRequest::class,
            System::MORPH_NAME => System::class,
            Message::MORPH_NAME => Message::class,
            Conversation::MORPH_NAME => Conversation::class,
            Company::MORPH_NAME => Company::class,
            PackingSlip::MORPH_NAME => PackingSlip::class,
        ];
    }

    protected function registerMorphMap(): void
    {
        Relation::morphMap(self::morphs());
    }
}
