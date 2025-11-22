<?php

namespace Tests\Feature\Mutations\Translation;

use App\Models\Admin\Admin;
use App\Types\Permissions;
use ErrorException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;
use Tests\Traits\Statuses;
use Tests\Traits\UserBuilder;

class SetTranslationsTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
    }

    /** @test */
    public function success()
    {
        $place = 'APP';

        $response = $this->postGraphQL(['query' => $this->getQueryStr($place)])
            ->assertOk();

        $responseData = $response->json('data.translationsSet');

        $this->assertArrayHasKey('status', $responseData);
        $this->assertArrayHasKey('message', $responseData);
        $this->assertArrayHasKey('hash', $responseData);

        $this->assertEmpty($responseData['message']);
        $this->assertTrue($responseData['status']);
        $this->assertNotEmpty($responseData['hash']);
    }


    public static function getQueryStr(
        string $place,
        string $key = 'alias',
        string $ru_text = 'test_ru',
        string $uk_text = 'test_uk'
    ): string
    {
        return sprintf('
            mutation {
                translationsSet(input:{
                    place: %s,
                    translations: [
                        { key: "%s", translation: [
                            {lang: "%s", text: "%s"},
                            {lang: "%s", text: "%s"}
                        ]}
                    ]
                }) {
                    status
                    message
                    hash
                }
            }',
            $place,
            $key,
            'ru',
            $ru_text,
            'uk',
            $uk_text
        );
    }
}
