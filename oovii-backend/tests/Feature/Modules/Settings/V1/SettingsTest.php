<?php

namespace Tests\Feature\Modules\Settings\V1;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use ReflectionMethod;
use Tests\TestCase;
use Tests\Traits\ResponseStructure;
use WezomCms\Core\Models\Setting;

class SettingsTest extends TestCase
{
    use DatabaseTransactions;
    use ResponseStructure;

    public function test_it_returns_list_of_success(): void
    {
        Setting::factory()->count(5)->create();
        $linksSettings = $this->createSocialLinksSettings();

        $settings = settings();
        $loadMethod = new ReflectionMethod($settings, 'load');
        $loadMethod->setAccessible(true);
        $loadMethod->invoke($settings);

        $res = $this->getJson(route('api.v1.mobile.settings'))
            ->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure($this->structureResource([
                '*' => [
                    'key',
                    'value',
                ]
            ]));

        $linksSettings = collect($linksSettings)
            ->map(fn (Setting $setting) => [
                'key' => "{$setting->module}.{$setting->group}.{$setting->key}",
                'value' => $setting->value
            ])
            ->values()
            ->toArray();

        self::assertEquals($linksSettings, $res->json('data'));
    }

    private function createSocialLinksSettings(): array
    {
        $links = [];
        $links['telegram'] = Setting::factory()->create([
            'module' => 'users',
            'group' => 'social_links',
            'key' => 'telegram_link',
        ]);
        $links['instagram'] = Setting::factory()->create([
            'module' => 'users',
            'group' => 'social_links',
            'key' => 'instagram_link',
        ]);
        $links['whatsapp'] = Setting::factory()->create([
            'module' => 'users',
            'group' => 'social_links',
            'key' => 'whatsapp_link',
        ]);

        return $links;
    }
}


