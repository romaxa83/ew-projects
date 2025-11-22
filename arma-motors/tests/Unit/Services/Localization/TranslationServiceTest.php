<?php

namespace Tests\Unit\Services\Localization;

use App\Models\Localization\Translation;
use App\Repositories\Localization\TranslationRepository;
use App\Services\Localizations\TranslationService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class TranslationServiceTest extends TestCase
{
    use DatabaseTransactions;

    private $service;
    private $repository;

    public function setUp(): void
    {
        parent::setUp();
        $this->service = app(TranslationService::class);
        $this->repository = app(TranslationRepository::class);
    }

    /** @test */
    public function success_one_create()
    {
        $key = 'test';
        $data = $this->dataOneTranslate(key: 'test');

        $translations = $this->repository->getByPlaceAndKey($data['place'], $key);
        $this->assertTrue($translations->isEmpty());

        $this->service->createOrUpdate($data);

        $translations = $this->repository->getByPlaceAndKey($data['place'], $key);
        $this->assertFalse($translations->isEmpty());
        $this->assertCount(2, $translations);

        $translation = $this->repository->getByPlaceAndKeyAndLang(
            $data['place'], $key, $data['translations'][0]['translation'][0]['lang']
        );

        $this->assertEquals($translation->text, $data['translations'][0]['translation'][0]['text']);
    }

    /** @test */
    public function success_one_update()
    {
        $key = 'test';
        $data = $this->dataOneTranslate(key: 'test');

        $this->service->createOrUpdate($data);

        $translationRu = $this->repository->getByPlaceAndKeyAndLang(
            $data['place'], $key, $data['translations'][0]['translation'][0]['lang']
        );
        $this->assertEquals($translationRu->text, $data['translations'][0]['translation'][0]['text']);

        $dataUpdate = $this->dataOneTranslate(key: 'test', ru_text: 'update');

        $this->service->createOrUpdate($dataUpdate);

        $translationRu->refresh();

        $this->assertNotEquals($translationRu->text, $data['translations'][0]['translation'][0]['text']);
        $this->assertEquals($translationRu->text, 'update');
    }

    /** @test */
    public function success_more_create()
    {
        $place = Translation::PLACE_ADMIN;
        $data = $this->dataMoreTranslate(key: 'test');

        $translations = $this->repository->getByPlace($place);
        $this->assertTrue($translations->isEmpty());

        $this->service->createOrUpdate($data);

        $translations = $this->repository->getByPlace($place);
        $this->assertFalse($translations->isEmpty());
        $this->assertCount(6, $translations);
    }

    /** @test */
    public function invalid_place_for_create()
    {
        $place = 'some_place';
        $data = $this->dataMoreTranslate(place: $place);

        $this->expectException(\Exception::class);

        $this->service->createOrUpdate($data);
    }

    /** @test */
    public function success_delete()
    {
        $key = 'key';
        $place = Translation::PLACE_ADMIN;
        $data = $this->dataMoreTranslate(key: $key, place: $place);

        $this->service->createOrUpdate($data);

        $translations = $this->repository->getByPlace($place);
        $this->assertCount(6, $translations);

        $this->service->createOrUpdate($this->dataDeleteTranslate(key: $key, place: $place));

        $translations = $this->repository->getByPlace($place);
        $this->assertCount(4, $translations);
    }

    /** @test */
    public function not_change_hash()
    {
        $place = Translation::PLACE_ADMIN;
        $data = $this->dataMoreTranslate(place: $place);

        $this->service->createOrUpdate($data);

        $hash = $this->service->getHashByPlace($place);

        $this->service->createOrUpdate($data);

        $newHash = $this->service->getHashByPlace($place);

        $this->assertEquals($hash, $newHash);
    }

    /** @test */
    public function change_hash()
    {
        $place = Translation::PLACE_ADMIN;
        $data = $this->dataMoreTranslate(place: $place);

        $this->service->createOrUpdate($data);

        $hash = $this->service->getHashByPlace($place);

        $data = $this->dataMoreTranslate(place: $place, key: 'new_key');
        $this->service->createOrUpdate($data);

        $newHash = $this->service->getHashByPlace($place);

        $this->assertNotEquals($hash, $newHash);
    }

    private function dataOneTranslate(
        string $place = Translation::PLACE_ADMIN,
        string $key = 'alias',
        string $ru_text = 'alias_ru',
        string $uk_text  = 'alias_uk'
    )
    {
        return [
            'place' => $place,
            'translations' => [
                [
                    'key' => $key,
                    'translation' => [
                        [
                            'lang' => 'ru',
                            'text' => $ru_text,
                        ],
                        [
                            'lang' => 'uk',
                            'text' => $uk_text,
                        ]
                    ]
                ]
            ]

        ];
    }

    private function dataDeleteTranslate(
        string $place = Translation::PLACE_ADMIN,
        string $key = 'alias',
    )
    {
        return [
            'place' => $place,
            'translations' => [
                [
                    'key' => $key,
                    'translation' => []
                ]
            ]

        ];
    }

    private function dataMoreTranslate(
        string $place = Translation::PLACE_ADMIN,
        string $key = 'alias',
        string $ru_text = 'alias_ru',
        string $uk_text  = 'alias_uk'
    )
    {
        return [
            'place' => $place,
            'translations' => [
                [
                    'key' => $key,
                    'translation' => [
                        [
                            'lang' => 'ru',
                            'text' => $ru_text,
                        ],
                        [
                            'lang' => 'uk',
                            'text' => $uk_text,
                        ]
                    ]
                ],
                [
                    'key' => $key . '_1',
                    'translation' => [
                        [
                            'lang' => 'ru',
                            'text' => $ru_text . '_1',
                        ],
                        [
                            'lang' => 'uk',
                            'text' => $uk_text . '_1',
                        ]
                    ]
                ],
                [
                    'key' => $key . '_2',
                    'translation' => [
                        [
                            'lang' => 'ru',
                            'text' => $ru_text . '_2',
                        ],
                        [
                            'lang' => 'uk',
                            'text' => $uk_text . '_2',
                        ]
                    ]
                ]
            ]

        ];
    }
}

