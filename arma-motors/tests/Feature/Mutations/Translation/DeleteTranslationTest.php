<?php

namespace Tests\Feature\Mutations\Translation;

use App\Models\Admin\Admin;
use App\Services\Localizations\TranslationService;
use App\Types\Permissions;
use ErrorException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature\Queries\Localization\TranslationTest;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;
use Tests\Traits\Statuses;
use Tests\Traits\UserBuilder;

class DeleteTranslationTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
    }

    /** @test */
    public function success_one_delete()
    {
        $place = 'APP';
        $key = 'test_alias';
        // делаем запрос на добавление переводов
        $data = TranslationTest::dataMoreTranslate('app', $key);
        app(TranslationService::class)->createOrUpdate($data);

        $responseLook = $this->graphQL(TranslationTest::getQueryStrByPlaceWithLang($place, [$data['translations'][0]['translation'][0]['lang']]));

        $responseLookData = $responseLook->json('data.translations');
        $count = count($responseLook->json('data.translations'));

        $this->assertNotEmpty($responseLookData);

        $this->assertEquals($responseLookData['0']['key'], $key);
        // удаляем перевод
        $responseDelete = $this->graphQL(self::getQueryStr($place, $key));
        $responseData = $responseDelete->json('data.translationsDelete');

        $this->assertArrayHasKey('status', $responseData);
        $this->assertArrayHasKey('message', $responseData);
        $this->assertArrayHasKey('hash', $responseData);

        // получаем переводы, проверяем что удаленных нет
        $responseLook = $this->graphQL(TranslationTest::getQueryStrByPlaceWithLang($place, [$data['translations'][0]['translation'][0]['lang']]));
        $responseLookData = $responseLook->json('data.translations');
        $this->assertNotEmpty($responseLookData);
        $this->assertNotEquals($responseLookData['0']['key'], $key);
        $this->assertNotEquals($count, count($responseLookData));
        $this->assertEquals($count, count($responseLookData) + 1);
    }

    /** @test */
    public function success_delete_by_place()
    {
        $place = 'APP';
        // делаем запрос нга добавление переводов
        $data = TranslationTest::dataMoreTranslate('app');
        app(TranslationService::class)->createOrUpdate($data);

        $responseLook = $this->graphQL(TranslationTest::getQueryStrByPlaceWithLang($place, [$data['translations'][0]['translation'][0]['lang']]));

        $responseLookData = $responseLook->json('data.translations');

        $this->assertNotEmpty($responseLookData);

        $responseDelete = $this->graphQL(self::getQueryStrWithoutKey($place));
        $responseData = $responseDelete->json('data.translationsDelete');

        $this->assertArrayHasKey('status', $responseData);
        $this->assertArrayHasKey('message', $responseData);
        $this->assertArrayHasKey('hash', $responseData);

        // получаем переводы
        $responseLook = $this->graphQL(TranslationTest::getQueryStrByPlaceWithLang($place, [$data['translations'][0]['translation'][0]['lang']]));

        $responseLookData = $responseLook->json('data.translations');
        $this->assertEmpty($responseLookData);
    }

    /** @test */
    public function not_found_key()
    {
        $place = 'APP';
        $key = 'test_alias';
        // делаем запрос нга добавление переводов
        $data = TranslationTest::dataMoreTranslate('app', $key);
        app(TranslationService::class)->createOrUpdate($data);

        $responseLook = $this->graphQL(TranslationTest::getQueryStrByPlaceWithLang($place, [$data['translations'][0]['translation'][0]['lang']]));

        $responseLookData = $responseLook->json('data.translations');
        $count = count($responseLook->json('data.translations'));

        $this->assertNotEmpty($responseLookData);

        $this->assertEquals($responseLookData['0']['key'], $key);

        $responseDelete = $this->graphQL(self::getQueryStr($place, 'another_key'));
        $responseData = $responseDelete->json('data.translationsDelete');

        $this->assertArrayHasKey('status', $responseData);
        $this->assertArrayHasKey('message', $responseData);
        $this->assertArrayHasKey('hash', $responseData);

        // получаем переводы
        $responseLook = $this->graphQL(TranslationTest::getQueryStrByPlaceWithLang($place, [$data['translations'][0]['translation'][0]['lang']]));

        $responseLookData = $responseLook->json('data.translations');
        $this->assertNotEmpty($responseLookData);
        $this->assertEquals($responseLookData['0']['key'], $key);
        $this->assertEquals($count, count($responseLookData));
    }

    public static function getQueryStr(
        string $place,
        string $key = 'alias'
    ): string
    {
        return sprintf('
            mutation {
                translationsDelete(input:{
                    place: %s,
                    key: "%s"
                }) {
                    status
                    message
                    hash
                }
            }',
            $place,
            $key
        );
    }

    public static function getQueryStrWithoutKey(
        string $place
    ): string
    {
        return sprintf('
            mutation {
                translationsDelete(input:{
                    place: %s
                }) {
                    status
                    message
                    hash
                }
            }',
            $place
        );
    }
}
