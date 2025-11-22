<?php

namespace Tests\Unit\Dto\Catalog;

use App\Dto\Catalog\CategoryDto;
use App\Dto\Catalog\Products\ProductDto;
use App\Dto\Catalog\Products\ProductTranslationDto;
use App\Exceptions\AssertDataException;
use App\Models\Catalog\Categories\Category;
use App\Models\Catalog\Certificates\Certificate;
use App\Models\Catalog\Features\Feature;
use App\Models\Catalog\Features\Value;
use App\Models\Catalog\Products\Product;
use App\Models\Catalog\Products\ProductSerialNumber;
use App\Models\Catalog\Videos\VideoLink;
use App\Models\Localization\Language;
use Core\Testing\GraphQL\Scalar\EnumValue;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ProductDtoTest extends TestCase
{
    use DatabaseTransactions;

    public function success_fill_by_args(): void
    {
        $data = static::data();

        $dto = ProductDto::byArgs($data);

        $this->assertEquals($dto->getActive(), $data['active']);
        $this->assertEquals($dto->getCategoryId(), $data['category_id']);

        $this->assertIsArray($dto->getVideoLinkIds());
        $this->assertNotEmpty($dto->getVideoLinkIds());

        $this->assertIsArray($dto->getRelationProducts());
        $this->assertNotEmpty($dto->getRelationProducts());

        $this->assertIsArray($dto->getCertificateIds());
        $this->assertNotEmpty($dto->getCertificateIds());

        $this->assertNotEmpty($dto->getTranslations());
        $this->assertIsArray($dto->getTranslations());

        foreach ($dto->getTranslations() as $key => $translation) {
            $this->assertInstanceOf(ProductTranslationDto::class, $translation);
            $this->assertEquals(
                $translation->getDescription(),
                $data['translations'][$translation->getLanguage()]['description']
            );
            $this->assertEquals($translation->getLanguage(), $data['translations'][$translation->getLanguage()]['language']);
        }

        $this->assertNotEmpty($dto->getFeatureValues());
        $this->assertIsArray($dto->getFeatureValues());
        foreach ($dto->getFeatureValues() as $key => $feature) {
            $this->assertEquals($feature, $data['features'][$key]['value_id']);
        }
    }

    /** @test */
    public function success_fill_only_required(): void
    {
        $data = static::data();
        unset(
            $data['sort'],
            $data['active'],
            $data['video_link_ids'],
            $data['troubleshoot_ids'],
            $data['certificate_ids'],
            $data['relations'],
            $data['features'],
        );

        $dto = ProductDto::byArgs($data);

        $this->assertEquals($dto->getActive(), Product::DEFAULT_ACTIVE);
        $this->assertEquals($dto->getCategoryId(), $data['category_id']);

        $this->assertIsArray($dto->getVideoLinkIds());
        $this->assertEmpty($dto->getVideoLinkIds());

        $this->assertIsArray($dto->getRelationProducts());
        $this->assertEmpty($dto->getRelationProducts());

        $this->assertIsArray($dto->getCertificateIds());
        $this->assertEmpty($dto->getCertificateIds());

        $this->assertNotEmpty($dto->getTranslations());
        $this->assertIsArray($dto->getTranslations());

        $this->assertEmpty($dto->getFeatureValues());
        $this->assertIsArray($dto->getFeatureValues());
    }

    public static function data(): array
    {
        $certs = Certificate::factory()
            ->count(2)
            ->create();

        $videoLinks = VideoLink::factory()
            ->count(3)
            ->create();

        $feature1 = Feature::factory()
            ->has(Value::factory())
            ->create();

        $feature2 = Feature::factory()
            ->has(Value::factory())
            ->create();

        $category = Category::factory()->create();

        $relationProduct1 = Product::factory()
            ->has(ProductSerialNumber::factory(), 'serialNumbers')
            ->create();

        $relationProduct2 = Product::factory()
            ->has(ProductSerialNumber::factory(), 'serialNumbers')
            ->create();

        $relationProduct3 = Product::factory()
            ->has(ProductSerialNumber::factory(), 'serialNumbers')
            ->create();

        return [
            'sort' => 2,
            'active' => false,
            'title' => 'some title',
            'slug' => 'some-slug',
            'title_metaphone' => 'sometitle',
            'category_id' => $category->id,
            'video_link_ids' => [
                $videoLinks[0]->id,
                $videoLinks[1]->id,
                $videoLinks[2]->id,
            ],
            'certificate_ids' => [
                $certs[0]->id,
                $certs[1]->id
            ],
            'relations' => [
                $relationProduct1->id,
                $relationProduct2->id,
                $relationProduct3->id
            ],
            'features' => [
                [
                    'id' => $feature1->id,
                    'value_id' => $feature1->values()->first()->id
                ],
                [
                    'id' => $feature2->id,
                    'value_id' => $feature2->values()->first()->id
                ],
            ],
            'translations' => languages()->mapWithKeys(
                fn (Language $language) => [
                    $language->slug => [
                        'language' => new EnumValue($language->slug),
                        'description' => 'description',
                        'seo_title' => 'seo title',
                        'seo_description' => 'seo description',
                        'seo_h1' => 'seo h1',
                    ]
                ]
            )->toArray()
        ];
    }

    /** @test */
    public function success_fill_by_args_without_active(): void
    {
        $data = static::data();
        unset($data['active']);

        $dto = ProductDto::byArgs($data);

        $this->assertEquals($dto->getActive(), Product::DEFAULT_ACTIVE);
    }

    /** @test */
    public function success_fill_by_args_without_video_links(): void
    {
        $data = static::data();
        unset($data['video_link_ids']);

        $dto = ProductDto::byArgs($data);

        $this->assertIsArray($dto->getVideoLinkIds());
        $this->assertEmpty($dto->getVideoLinkIds());
    }

    /** @test */
    public function success_fill_by_args_without_relations(): void
    {
        $data = static::data();
        unset($data['relations']);

        $dto = ProductDto::byArgs($data);

        $this->assertIsArray($dto->getRelationProducts());
        $this->assertEmpty($dto->getRelationProducts());
    }

    /** @test */
    public function success_fill_by_args_without_features(): void
    {
        $data = static::data();
        unset($data['features']);

        $dto = ProductDto::byArgs($data);

        $this->assertIsArray($dto->getFeatureValues());
        $this->assertEmpty($dto->getFeatureValues());
    }

    /** @test */
    public function fail_without_translations(): void
    {
        $data = static::data();
        unset($data['translations']);

        $this->expectException(AssertDataException::class);
        $this->expectExceptionMessage(__('exceptions.assert_data.field must exist', ['field' => 'translations']));

        CategoryDto::byArgs($data);
    }
}

