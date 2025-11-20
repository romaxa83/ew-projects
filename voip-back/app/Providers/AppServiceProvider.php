<?php

namespace App\Providers;

use App\Models\Musics\Music;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;
use Laravel\Telescope\Watchers\RequestWatcher;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            RequestWatcher::class,
            \App\Services\Telescope\Watchers\RequestWatcher::class
        );
    }

    public function boot(): void
    {
        $this->registerMorphMap();
    }

    public static function morphs(): array
    {
        return [
            Music::MORPH_NAME => Music::class,
        ];
    }

    protected function registerMorphMap(): void
    {
        Relation::morphMap(self::morphs());
    }
}
