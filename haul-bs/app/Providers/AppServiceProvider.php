<?php

namespace App\Providers;

use App\Foundations\Modules\Seo\Models\Seo;
use App\Models\Customers\Customer;
use App\Models\Inventories\Brand;
use App\Models\Inventories\Category;
use App\Models\Inventories\Features\Feature;
use App\Models\Inventories\Features\Value;
use App\Models\Inventories\Inventory;
use App\Models\Orders;
use App\Models\Settings\Settings;
use App\Models\Users\User;
use App\Models\Vehicles\Trailer;
use App\Models\Vehicles\Truck;
use App\Services\Vehicles\DecoderVin\VinDecodeService;
use App\Services\Vehicles\DecoderVin\VpicVinDecodeService;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;
use Validator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(VinDecodeService::class, VpicVinDecodeService::class);
    }

    public function boot(): void
    {
        $this->registerMorphMap();

        $this->customValidatorExtends();
    }

    public static function morphs(): array
    {
        return [
            User::MORPH_NAME => User::class,
            Customer::MORPH_NAME => Customer::class,
            Settings::MORPH_NAME => Settings::class,
            Truck::MORPH_NAME => Truck::class,
            Trailer::MORPH_NAME => Trailer::class,
            Category::MORPH_NAME => Category::class,
            Brand::MORPH_NAME => Brand::class,
            Feature::MORPH_NAME => Feature::class,
            Value::MORPH_NAME => Value::class,
            Inventory::MORPH_NAME => Inventory::class,
            Seo::MORPH_NAME => Seo::class,
            Orders\BS\Order::MORPH_NAME => Orders\BS\Order::class,
            Orders\Parts\Order::MORPH_NAME => Orders\Parts\Order::class,
        ];
    }

    protected function registerMorphMap(): void
    {
        Relation::morphMap(self::morphs());
    }

    protected function customValidatorExtends(): void
    {
        Validator::extend('alpha_spaces',
            fn ($attribute, $value) => preg_match('/^[a-zA-Z\s]*$/u', $value)
        );
    }
}
