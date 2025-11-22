<?php

namespace Tests\Unit\Testing\GraphQL\QueryBuilder;

use App\Models\Catalog\Products\Product;
use Core\Enums\Messages\MessageTypeEnum;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Core\Testing\GraphQL\Scalar\EnumValue;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use JsonException;
use Tests\TestCase;
use Tests\Traits\Storage\TestStorage;

class QueryBuilderTest extends TestCase
{
    use DatabaseTransactions;
    use TestStorage;

    protected const TEST_QUERY = 'test_query_name';

    protected const TEST_MUTATION = 'test_mutation_name';

    public function test_builder_query(): void
    {
        $query = GraphQLQuery::query(self::TEST_QUERY)
            ->args(['id' => 1])
            ->select(['id'])
            ->make();

        self::assertEquals(
            [
                "query" => "query {test_query_name(id: 1){id}}"
            ],
            $query
        );
    }

    public function test_builder_mutation(): void
    {
        $query = GraphQLQuery::mutation(self::TEST_QUERY)
            ->args(['id' => 1])
            ->select(['id'])
            ->make();

        self::assertEquals(
            [
                "query" => "mutation {test_query_name(id: 1){id}}"
            ],
            $query
        );
    }

    public function test_builder_empty_query(): void
    {
        $query = GraphQLQuery::query(self::TEST_QUERY)
            ->make();

        self::assertEquals(
            [
                "query" => "query {test_query_name}"
            ],
            $query
        );
    }

    public function test_plain_query(): void
    {
        $query = new GraphQLQuery(self::TEST_QUERY);

        self::assertEquals(
            sprintf('{%s}', self::TEST_QUERY),
            (string)$query
        );
    }

    public function test_scalar_argument(): void
    {
        $query = new GraphQLQuery(
            self::TEST_QUERY,
            [
                'id' => 1
            ]
        );

        $this->assertEquals(
            sprintf('{%s(id: %s)}', self::TEST_QUERY, 1),
            (string)$query
        );

        $query = new GraphQLQuery(
            self::TEST_QUERY,
            [
                'id' => '1'
            ]
        );

        $this->assertEquals(
            sprintf('{%s(id: "%s")}', self::TEST_QUERY, 1),
            (string)$query
        );

        $query = new GraphQLQuery(
            self::TEST_QUERY,
            [
                'field' => 'string value'
            ]
        );

        $this->assertEquals(
            sprintf('{%s(field: "string value")}', self::TEST_QUERY),
            (string)$query
        );
    }

    public function test_enum_argument(): void
    {
        $query = new GraphQLQuery(
            self::TEST_QUERY,
            [
                'enum' => new EnumValue('value')
            ]
        );

        $this->assertEquals(
            sprintf('{%s(enum: value)}', self::TEST_QUERY),
            (string)$query
        );
    }

    public function test_scalar_list_argument(): void
    {
        $query = new GraphQLQuery(
            self::TEST_QUERY,
            [
                'ids' => [1, 2, 3],
                'enums' => [new EnumValue('value1'), new EnumValue('value2')]
            ]
        );

        $this->assertEquals(
            sprintf('{%s(ids: [%s, %s, %s], enums: [value1, value2])}', self::TEST_QUERY, 1, 2, 3),
            (string)$query
        );
    }

    public function test_object_argument(): void
    {
        $query = new GraphQLQuery(
            self::TEST_QUERY,
            [
                'object' => [
                    'field_1' => 'value_1',
                    'field_2' => 'value_2'
                ]
            ]
        );

        $this->assertEquals(
            sprintf('{%s(object: {field_1: "value_1", field_2: "value_2"})}', self::TEST_QUERY),
            (string)$query
        );
    }

    public function test_object_with_list_of_objects_argument(): void
    {
        $query = new GraphQLQuery(
            self::TEST_QUERY,
            [
                'object' => [
                    [
                        'field_1' => 'value_1',
                        'field_2' => 'value_2'
                    ],
                    [
                        'field_3' => 'value_3',
                        'field_4' => 'value_4'
                    ]
                ]
            ]
        );

        $this->assertEquals(
            sprintf(
                '{%s(object: [{field_1: "value_1", field_2: "value_2"}, {field_3: "value_3", field_4: "value_4"}])}',
                self::TEST_QUERY
            ),
            (string)$query
        );
    }

    /**
     * @throws JsonException
     */
    public function test_single_file_upload(): void
    {
        $file = UploadedFile::fake()->image('test.png');

        $query = new GraphQLQuery(
            self::TEST_QUERY,
            [
                'field' => 'string value',
                'file' => $file
            ]
        );

        $upload = $query->getUploadMutation();

        $this->assertEquals(
            sprintf(
                '{"query":"mutation ($file: Upload!) {%s(field: \"string value\", file: $file)}"}',
                self::TEST_QUERY
            ),
            $upload['operations']
        );

        self::assertEquals(
            '{ "file": ["variables.file"] }',
            $upload['map']
        );

        self::assertEquals($file, $upload['file']);
    }

    /**
     * @throws JsonException
     */
    public function test_multi_file_upload(): void
    {
        $this->fakeMediaStorage();

        $product = Product::factory()->create();

        $image1 = UploadedFile::fake()->image('product1.jpg');
        $image2 = UploadedFile::fake()->image('product2.jpg');
        $image3 = UploadedFile::fake()->image('product3.jpg');
        $image4 = UploadedFile::fake()->image('product4.jpg');

        $query = new GraphQLQuery(
            self::TEST_MUTATION,
            [
                'model_id' => $product->id,
                'model_type' => new EnumValue($product::MORPH_NAME),
                'media' => [$image1, $image2, $image3, $image4],
            ]
        );

        $upload = $query->getUploadMutation();

        $this->assertEquals(
            sprintf(
                '{"query":"mutation ($media: Upload!) {%s(model_id: %d, model_type: product, media: [$media])}"}',
                self::TEST_MUTATION,
                $product->id
            ),
            $upload['operations']
        );

        self::assertEquals(
            '{ "media": ["variables.media"] }',
            $upload['map']
        );

        self::assertEquals(
            [$image1, $image2, $image3, $image4],
            $upload['media']
        );
    }

    public function test_complex_without_files(): void
    {
        $query = new GraphQLQuery(
            self::TEST_QUERY,
            [
                'int' => 1,
                's_int' => '1',
                'string' => 'string',
                'bool_true' => true,
                'bool_false' => false,
                'enum' => new EnumValue('enum'),
                'base_enum' => MessageTypeEnum::SUCCESS(),
                'scalar_int_list' => [1, 2, 3],
                'scalar_string_list' => ['s1', 's2', 's3'],
                'object' => [
                    'field1' => 'value1',
                    'field2' => 'value2',
                ],
                'list_of_objects' => [
                    [
                        'field3' => 'value3',
                        'field4' => 'value4',
                    ],
                    [
                        'field5' => 'value5',
                        'field6' => 'value6',
                    ]
                ],
            ]
        );

        self::assertEquals(
            '{test_query_name(int: 1, s_int: "1", string: "string", bool_true: true, bool_false: false, enum: enum, base_enum: success, scalar_int_list: [1, 2, 3], scalar_string_list: ["s1", "s2", "s3"], object: {field1: "value1", field2: "value2"}, list_of_objects: [{field3: "value3", field4: "value4"}, {field5: "value5", field6: "value6"}])}',
            (string)$query
        );
    }
}
