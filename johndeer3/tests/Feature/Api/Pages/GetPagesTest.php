<?php

namespace Tests\Feature\Api\Pages;

use App\Models\Page\Page;
use App\Repositories\PageRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery\MockInterface;
use Tests\Builder\PageBuilder;
use Tests\Builder\UserBuilder;
use Tests\TestCase;
use Tests\Traits\ResponseStructure;

class GetPagesTest extends TestCase
{
    use DatabaseTransactions;
    use ResponseStructure;

    protected $userBuilder;
    protected $pageBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
        $this->userBuilder = resolve(UserBuilder::class);
        $this->pageBuilder = resolve(PageBuilder::class);
    }

    /** @test */
    public function success()
    {
        list($en, $ua) = ['en', 'ua'];
        $page_pp = $this->pageBuilder->setAlias(Page::ALIAS_PRIVATE_POLICY)
            ->withTranslations($en, $ua)
            ->create();

        $page_disclaimer = $this->pageBuilder->setAlias(Page::ALIAS_DISCLAIMER)
            ->withTranslations($en, $ua)
            ->create();

        $page_pp->refresh();

        $this->getJson(route('api.pages'))
            ->assertJson($this->structureSuccessResponse([
                [
                    "id" => $page_pp->id,
                    "alias" => $page_pp->alias,
                    "translations" => [
                        [
                            "lang" => $en,
                            "title" => $page_pp->translations->where('lang', $en)->first()->name,
                            "text" => $page_pp->translations->where('lang', $en)->first()->text
                        ],
                        [
                            "lang" => $ua,
                            "title" => $page_pp->translations->where('lang', $ua)->first()->name,
                            "text" => $page_pp->translations->where('lang', $ua)->first()->text
                        ]
                    ]
                ],
                [
                    "id" => $page_disclaimer->id,
                    "alias" => $page_disclaimer->alias,
                    "translations" => [
                        [
                            "lang" => $en,
                            "title" => $page_disclaimer->translations->where('lang', $en)->first()->name,
                            "text" => $page_disclaimer->translations->where('lang', $en)->first()->text
                        ],
                        [
                            "lang" => $ua,
                            "title" => $page_disclaimer->translations->where('lang', $ua)->first()->name,
                            "text" => $page_disclaimer->translations->where('lang', $ua)->first()->text
                        ]
                    ]
                ]
            ]))
            ->assertJsonCount(2, 'data')
            ->assertJsonCount(2, 'data.0.translations')
            ->assertJsonCount(2, 'data.1.translations')
        ;
    }

    /** @test */
    public function success_query_alias()
    {
        list($en, $ua) = ['en', 'ua'];
        $page_pp = $this->pageBuilder->setAlias(Page::ALIAS_PRIVATE_POLICY)
            ->withTranslations($en, $ua)
            ->create();

        $page_disclaimer = $this->pageBuilder->setAlias(Page::ALIAS_DISCLAIMER)
            ->withTranslations($en, $ua)
            ->create();

        $page_pp->refresh();

        $this->getJson(route('api.pages', [
            "alias" => Page::ALIAS_DISCLAIMER
        ]))
            ->assertJson($this->structureSuccessResponse([
                ["id" => $page_disclaimer->id]
            ]))
            ->assertJsonCount(1, 'data')
            ->assertJsonCount(2, 'data.0.translations')
        ;

        $this->getJson(route('api.pages', [
            "alias" => Page::ALIAS_PRIVATE_POLICY
        ]))
            ->assertJson($this->structureSuccessResponse([
                ["id" => $page_pp->id]
            ]))
            ->assertJsonCount(1, 'data')
            ->assertJsonCount(2, 'data.0.translations')
        ;
    }

    /** @test */
    public function success_query_lang()
    {
        list($en, $ua) = ['en', 'ua'];
        $page_pp = $this->pageBuilder->setAlias(Page::ALIAS_PRIVATE_POLICY)
            ->withTranslations($en, $ua)
            ->create();

        $page_disclaimer = $this->pageBuilder->setAlias(Page::ALIAS_DISCLAIMER)
            ->withTranslations($en, $ua)
            ->create();

        $this->getJson(route('api.pages', [
            'lang' => 'en'
        ]))
            ->assertJson($this->structureSuccessResponse([
                [
                    "id" => $page_pp->id,
                    "alias" => $page_pp->alias,
                    "translations" => [
                        [
                            "lang" => $en,
                            "title" => $page_pp->translations->where('lang', $en)->first()->name,
                            "text" => $page_pp->translations->where('lang', $en)->first()->text
                        ]
                    ]
                ],
                [
                    "id" => $page_disclaimer->id,
                    "alias" => $page_disclaimer->alias,
                    "translations" => [
                        [
                            "lang" => $en,
                            "title" => $page_disclaimer->translations->where('lang', $en)->first()->name,
                            "text" => $page_disclaimer->translations->where('lang', $en)->first()->text
                        ]
                    ]
                ]
            ]))
            ->assertJsonCount(2, 'data')
            ->assertJsonCount(1, 'data.0.translations')
            ->assertJsonCount(1, 'data.1.translations')
        ;
    }

    /** @test */
    public function success_query_lang_as_array()
    {
        list($en, $ua, $pl) = ['en', 'ua', 'pl'];
        $page_pp = $this->pageBuilder->setAlias(Page::ALIAS_PRIVATE_POLICY)
            ->withTranslations($en, $ua, $pl)
            ->create();

        $page_disclaimer = $this->pageBuilder->setAlias(Page::ALIAS_DISCLAIMER)
            ->withTranslations($en, $ua, $pl)
            ->create();

        $this->getJson(route('api.pages', [
            'alias' => Page::ALIAS_DISCLAIMER,
            'lang' => ['en', 'pl'],
        ]))
            ->assertJson($this->structureSuccessResponse([
                [
                    "id" => $page_disclaimer->id,
                    "alias" => $page_disclaimer->alias,
                    "translations" => [
                        [
                            "lang" => $en,
                            "title" => $page_disclaimer->translations->where('lang', $en)->first()->name,
                            "text" => $page_disclaimer->translations->where('lang', $en)->first()->text
                        ],
                        [
                            "lang" => $pl,
                            "title" => $page_disclaimer->translations->where('lang', $pl)->first()->name,
                            "text" => $page_disclaimer->translations->where('lang', $pl)->first()->text
                        ]
                    ]
                ]
            ]))
            ->assertJsonCount(1, 'data')
            ->assertJsonCount(2, 'data.0.translations')
        ;
    }

    /** @test */
    public function success_query_lang_as_array_if_not_some_lang()
    {
        list($en, $ua, $pl) = ['en', 'ua', 'pl'];
        $page_pp = $this->pageBuilder->setAlias(Page::ALIAS_PRIVATE_POLICY)
            ->withTranslations($en, $ua, $pl)
            ->create();

        $page_disclaimer = $this->pageBuilder->setAlias(Page::ALIAS_DISCLAIMER)
            ->withTranslations($en, $ua, $pl)
            ->create();

        $this->getJson(route('api.pages', [
            'alias' => Page::ALIAS_DISCLAIMER,
            'lang' => ['en', 'wrong'],
        ]))
            ->assertJson($this->structureSuccessResponse([
                [
                    "id" => $page_disclaimer->id,
                    "alias" => $page_disclaimer->alias,
                    "translations" => [
                        [
                            "lang" => $en,
                            "title" => $page_disclaimer->translations->where('lang', $en)->first()->name,
                            "text" => $page_disclaimer->translations->where('lang', $en)->first()->text
                        ],
                    ]
                ]
            ]))
            ->assertJsonCount(1, 'data')
            ->assertJsonCount(1, 'data.0.translations')
        ;
    }

    /** @test */
    public function success_only_active_page()
    {
        list($en, $ua) = ['en', 'ua'];
        $page_pp = $this->pageBuilder->setAlias(Page::ALIAS_PRIVATE_POLICY)
            ->withTranslations($en, $ua)
            ->create();

        $page_disclaimer = $this->pageBuilder->setAlias(Page::ALIAS_DISCLAIMER)
            ->withTranslations($en, $ua)
            ->setActive(false)
            ->create();

        $this->getJson(route('api.pages'))
            ->assertJson($this->structureSuccessResponse([
                [
                    "id" => $page_pp->id,
                    "alias" => $page_pp->alias,
                ],
            ]))
            ->assertJsonCount(1, 'data')
            ->assertJsonCount(2, 'data.0.translations')
        ;
    }

    /** @test */
    public function success_return_all_if_wrong_type()
    {
        $this->pageBuilder->setAlias(Page::ALIAS_PRIVATE_POLICY)->create();
        $this->pageBuilder->setAlias(Page::ALIAS_DISCLAIMER)->create();

        $this->getJson(route('api.pages', ['type' => 'wrong']))
            ->assertJsonCount(2, 'data')
        ;
    }

    /** @test */
    public function fail_repo_return_exception()
    {
        $this->pageBuilder->setAlias(Page::ALIAS_PRIVATE_POLICY)->create();
        $this->pageBuilder->setAlias(Page::ALIAS_DISCLAIMER)->create();

        $this->mock(PageRepository::class, function(MockInterface $mock){
            $mock->shouldReceive("getAllWrap")
                ->andThrows(\Exception::class, "some exception message");
        });

        $this->getJson(route('api.pages'))
            ->assertJson($this->structureErrorResponse("some exception message"))
        ;
    }
}
