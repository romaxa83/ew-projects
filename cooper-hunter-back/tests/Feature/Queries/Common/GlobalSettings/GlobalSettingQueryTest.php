<?php

namespace Tests\Feature\Queries\Common\GlobalSettings;

use App\GraphQL\Queries\Common\GlobalSettings\BaseGlobalSettingQuery;
use App\Models\GlobalSettings\GlobalSetting;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class GlobalSettingQueryTest extends TestCase
{
    use DatabaseTransactions;

    public const QUERY = BaseGlobalSettingQuery::NAME;

    public function test_global_setting(): void
    {
        GlobalSetting::factory()->create();

        $query = new GraphQLQuery(
                    self::QUERY,
            select: [
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
                        'company_site',
                        'company_title',
                    ],
        );

        $jsonStructure = [
            'data' => [
                self::QUERY => [
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
                    'company_site',
                    'company_title',
                ]
            ],
        ];

        $this->postGraphQL($query->getQuery())
            ->assertOk()
            ->assertJsonStructure($jsonStructure);

        $this->postGraphQLBackOffice($query->getQuery())
            ->assertOk()
            ->assertJsonStructure($jsonStructure);
    }
}
