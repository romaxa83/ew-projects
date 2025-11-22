<?php

namespace Tests\Feature\Mutations\BackOffice\GlobalSettings;

use App\GraphQL\Mutations\BackOffice\GlobalSettings\GlobalSettingUpdateMutation;
use App\Models\Admins\Admin;
use App\Models\GlobalSettings\GlobalSetting;
use App\Permissions\GlobalSettings\GlobalSettingUpdatePermission;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\Permissions\AdminManagerHelperTrait;
use Tests\Traits\TranslationHelper;

class GlobalSettingUpdateTest extends TestCase
{
    use DatabaseTransactions;
    use AdminManagerHelperTrait;
    use TranslationHelper;

    public function test_update_global_setting(): void
    {
        $this->loginAsAdmin()
            ->assignRole(
                $this->generateRole('Admin manager', [GlobalSettingUpdatePermission::KEY], Admin::GUARD)
            );

        GlobalSetting::factory()->create();

        $this->assertDatabaseMissing(GlobalSetting::class, [
            'footer_address' => 'footer_address',
            'footer_email' => 'footer_email',
            'footer_phone' => 'footer_phone',
            'footer_instagram_link' => 'footer_instagram_link',
            'footer_meta_link' => 'footer_meta_link',
            'footer_twitter_link' => 'footer_twitter_link',
            'footer_youtube_link' => 'footer_youtube_link',
            'footer_additional_email' => 'footer_additional_email',
            'footer_app_store_link' => 'footer_app_store_link',
            'footer_google_pay_link' => 'footer_google_pay_link',
            'slider_countdown' => 10,
            'company_site' => 'company_site',
            'company_title' => 'company_title',
        ]);

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(GlobalSettingUpdateMutation::NAME)
                ->args(
                    [
                        'globalSetting' => [
                            'footer_address' => 'footer_address',
                            'footer_email' => 'footer_email',
                            'footer_phone' => 'footer_phone',
                            'footer_instagram_link' => 'footer_instagram_link',
                            'footer_meta_link' => 'footer_meta_link',
                            'footer_twitter_link' => 'footer_twitter_link',
                            'footer_youtube_link' => 'footer_youtube_link',
                            'footer_additional_email' => 'footer_additional_email',
                            'footer_app_store_link' => 'footer_app_store_link',
                            'footer_google_pay_link' => 'footer_google_pay_link',
                            'slider_countdown' => 10,
                            'company_site' => 'company_site',
                            'company_title' => 'company_title',
                        ]
                    ]
                )
                ->select(
                    [
                        'footer_address',
                        'footer_email',
                        'footer_phone',
                        'footer_instagram_link',
                        'footer_meta_link',
                        'footer_twitter_link',
                        'footer_youtube_link',
                        'footer_additional_email',
                        'footer_app_store_link',
                        'footer_google_pay_link',
                        'slider_countdown',
                        'company_site',
                        'company_title',
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        GlobalSettingUpdateMutation::NAME => [
                            'footer_address',
                            'footer_email',
                            'footer_phone',
                            'footer_instagram_link',
                            'footer_meta_link',
                            'footer_twitter_link',
                            'footer_youtube_link',
                            'footer_additional_email',
                            'footer_app_store_link',
                            'footer_google_pay_link',
                            'slider_countdown',
                            'company_site',
                            'company_title',
                        ]
                    ]
                ]
            );

        $this->assertDatabaseHas(
            GlobalSetting::class,
            [
                'footer_address' => 'footer_address',
                'footer_email' => 'footer_email',
                'footer_phone' => 'footer_phone',
                'footer_instagram_link' => 'footer_instagram_link',
                'footer_meta_link' => 'footer_meta_link',
                'footer_twitter_link' => 'footer_twitter_link',
                'footer_youtube_link' => 'footer_youtube_link',
                'footer_additional_email' => 'footer_additional_email',
                'footer_app_store_link' => 'footer_app_store_link',
                'footer_google_pay_link' => 'footer_google_pay_link',
                'slider_countdown' => 10,
                'company_site' => 'company_site',
                'company_title' => 'company_title',
            ]
        );
    }
}
