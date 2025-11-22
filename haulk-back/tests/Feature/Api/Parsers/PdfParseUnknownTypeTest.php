<?php

namespace Tests\Feature\Api\Parsers;

use Illuminate\Http\Response;

class PdfParseUnknownTypeTest extends PdfParserHelper
{

    protected function getFolderName(): string
    {
        return '';
    }

    public function test_it_error()
    {
        $this->sendPdfFile('other_system_bol')
            ->assertStatus(Response::HTTP_NOT_ACCEPTABLE)
            ->assertJson(
                [
                    'errors' => [
                        [
                            'title' => __('validation.custom.parser.file_is_not_identify'),
                            'status' => Response::HTTP_NOT_ACCEPTABLE,
                        ]
                    ]
                ]
            );
    }

}
