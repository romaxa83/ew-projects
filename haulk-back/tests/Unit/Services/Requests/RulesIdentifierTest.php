<?php

namespace Tests\Unit\Services\Requests;

use App\Services\Utilities\RulesIdentifyService;
use Tests\TestCase;

class RulesIdentifierTest extends TestCase
{
    /**
     * @param $rules
     * @param $requestData
     * @param $expected
     * @dataProvider identifyRulesDataProvider
     */
    public function test_it_identify_rules($rules, $requestData, $expected)
    {
        $this->assertEquals($expected, resolve(RulesIdentifyService::class)->identify($rules, $requestData));
    }

    public function identifyRulesDataProvider()
    {
        return [
            [
                [
                    'key' => ['rule',],
                    'key1' => ['rule1',],
                    'key2' => ['rule2'],
                    'key2.subkey2' => ['rule3'],
                ],
                [
                    'key1' => 'value1',
                    'key2' => ['value1'],
                ],
                [
                    'key1' => ['rule1',],
                    'key2' => ['rule2'],
                    'key2.subkey2' => ['rule3'],
                ]
            ],
            [
                [
                    'simplekey' => ['string'],
                    'key' => ['array'],
                    'key.array' => ['array'],
                    'key.*.subkey1' => ['string'],
                    'key.*.subkey2' => ['integer'],
                ],
                [
                    'key' => [
                        [
                            'subkey1' => 'string1',
                            'subkey2' => 'string2',
                        ]
                    ]
                ],
                [
                    'key' => ['array'],
                    'key.array' => ['array'],
                    'key.*.subkey1' => ['string'],
                    'key.*.subkey2' => ['integer'],
                ],
            ],
        ];
    }
}
