<?php

namespace App\Providers;

use App\Enums\Utilities\MorphModelNameEnum;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(): void
    {
        $this->registerMorphNames();
    }

    public function registerMorphNames(): void
    {
        $morphModels = MorphModelNameEnum::getInstances();
        $morphMap = [];

        foreach ($morphModels as $morphModel) {
            $morphMap[$morphModel->key] = $morphModel->value;
        }

        Relation::morphMap($morphMap);
    }
}
