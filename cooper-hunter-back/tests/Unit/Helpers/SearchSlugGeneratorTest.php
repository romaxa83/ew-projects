<?php

namespace Tests\Unit\Helpers;

use Tests\TestCase;

class SearchSlugGeneratorTest extends TestCase
{
    /**
     * @dataProvider SearchSlugDataProvider
     */
    public function test_generate_search_slug($title, $searchSlug): void
    {
        self::assertEquals($searchSlug, makeSearchSlug($title));
    }

    public function SearchSlugDataProvider(): array
    {
        return [
            'data_1' => ['CH-09SPH-115VI/CH-09SPH-115VO', 'ch09sph115vich09sph115vo'],
            'data_2' => ['CH-12SPH-115VI/CH-12SPH-115VO', 'ch12sph115vich12sph115vo'],
            'data_3' => ['CH-12SPH-115VI', 'ch12sph115vi'],
            'data_4' => ['CH-12SPH-115VO', 'ch12sph115vo'],
            'data_5' => ['CH-18MSPHFC-230VI', 'ch18msphfc230vi'],
            'data_6' => ['Some Name', 'somename'],
            'data_7' => ['Some 07 Name 48 with digits 002331', 'some07name48withdigits002331'],
            'data_8' => ['So_me * Name &~ with ? special , chars.', 'somenamewithspecialchars'],
            'special_chars' => ['~!@#$%^&*()_+{}[]|\\;:\'",<.>/?', ''],
            'digits' => ['1234567890', '1234567890'],
            'underscore' => ['words_With_underscore', 'wordswithunderscore'],
            'space' => ['       ', ''],
        ];
    }
}
