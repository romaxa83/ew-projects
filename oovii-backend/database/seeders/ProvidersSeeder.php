<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use WezomCms\Providers\Models\Provider;
use WezomCms\Providers\Services\ProviderService;
use WezomCms\Providers\Types\ProviderStatus;

class ProvidersSeeder extends Seeder
{
    public function run(): void
    {
        if (!$providers = Provider::count()) {
            $providerService = app(ProviderService::class);

            Provider::factory()
                ->count(5)
                ->create([
                    'status' => ProviderStatus::MODERATED,
                ])
                ->each(function (Provider $provider) use ($providerService) {
                    $providerService->createAdminProfile($provider);
                });
        }
    }
}
