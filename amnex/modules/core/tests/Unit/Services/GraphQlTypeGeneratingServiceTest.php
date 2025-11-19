<?php

declare(strict_types=1);

namespace Wezom\Core\Tests\Unit\Services;

use GraphQL\Type\Definition\PhpEnumType;
use Illuminate\Support\Collection;
use Nuwave\Lighthouse\Exceptions\DefinitionException;
use Nuwave\Lighthouse\Schema\TypeRegistry;
use ReflectionException;
use Wezom\Core\Enums\Images\ImageSizeEnum;
use Wezom\Core\Services\GraphQlTypeGeneratingService;
use Wezom\Core\Testing\TestCase;
use Wezom\Core\Tests\Unit\Source\EntityButtonTypeEnum;
use Wezom\Core\Tests\Unit\Source\EntityDesktopTextPositionEnum;
use Wezom\Core\Tests\Unit\Source\EntityTestingDto;
use Wezom\Core\Tests\Unit\Source\EntityTestingTranslationDto;
use Wezom\Core\Tests\Unit\Source\UploadsTestingDto;

class GraphQlTypeGeneratingServiceTest extends TestCase
{
    protected GraphQlTypeGeneratingService $service;
    protected TypeRegistry $typeRegistry;

    public function setUp(): void
    {
        parent::setUp();

        $this->service = $this->app->make(GraphQlTypeGeneratingService::class);
        $this->typeRegistry = $this->app->make(TypeRegistry::class);
    }

    /**
     * @throws ReflectionException
     * @throws DefinitionException
     */
    public function testGenerateInputFromDto()
    {
        $this->typeRegistry->register(new PhpEnumType(EntityButtonTypeEnum::class))
            ->register(new PhpEnumType(EntityDesktopTextPositionEnum::class));

        $translatedType = $this->service->generateInputFromDto(EntityTestingTranslationDto::class);

        $this->assertNotNull($translatedType);

        $this->assertEquals('EntityTestingTranslationInput', $translatedType->name());
        $this->assertNull($translatedType->description());
        $config = collect($translatedType->config['fields']);
        $this->assertCount(8, $config);

        $this->assertFieldType($config, 'language', 'String!');

        $this->typeRegistry->register($translatedType);

        $rootType = $this->service->generateInputFromDto(EntityTestingDto::class);
        $this->assertNotNull($rootType);

        $this->assertEquals('EntityTestingInput', $rootType->name());
        $this->assertEquals('Entity description', $rootType->description());
        $rootConfig = collect($rootType->config['fields']);
        $this->assertCount(13, $rootConfig);

        $this->assertFieldType($rootConfig, 'videoLink', 'String');
        $headerSizeType = $rootConfig->firstOrFail('name', 'headerSize');
        $this->assertEquals('header size description', $headerSizeType['description']);
        $this->assertFieldType($rootConfig, 'headerSize', 'Int');
        $this->assertFieldType($rootConfig, 'footerSize', 'Int!');
        $this->assertFieldType($rootConfig, 'backgroundOverlay', 'Boolean!');
        $this->assertFieldType($rootConfig, 'desktopTextPosition', 'EntityDesktopTextPositionEnum');
        $this->assertFieldType($rootConfig, 'buttonType', 'EntityButtonTypeEnum!');
        $this->assertFieldType($rootConfig, 'translations', '[EntityTestingTranslationInput!]!');
        $this->assertFieldType($rootConfig, 'userIds', '[ID!]!');
        $this->assertFieldType($rootConfig, 'otherIds', '[ID!]!');
        $this->assertFieldType($rootConfig, 'managerIds', '[ID!]!');
        $this->assertFieldType($rootConfig, 'ownerIds', '[ID!]!');
        $this->assertFieldType($rootConfig, 'managerId', 'ID!');
        $this->assertFieldType($rootConfig, 'clientId', 'ID!');
    }

    private function assertFieldType(Collection $config, string $fieldName, string $grahQlType): void
    {
        $languageField = $config->where('name', $fieldName)->firstOrFail();

        $this->assertEquals($grahQlType, $languageField['type']->toString());
    }

    public function testGraphQlTypeAttribute(): void
    {
        $translatedType = $this->service->generateInputFromDto(EntityTestingTranslationDto::class);

        $this->assertNotNull($translatedType);

        $config = collect($translatedType->config['fields']);

        $this->assertFieldType($config, 'rootCarbon', 'DateForFront!');
        $this->assertFieldType($config, 'supportCarbon', 'DateTimeForFront!');
        $this->assertFieldType($config, 'dates', '[DateForFront!]!');
        $this->assertFieldType($config, 'ids', '[Float!]!');

        $this->assertNull($config->firstWhere('name', 'employeeId'));
    }

    public function testGenerateImageDescription(): void
    {
        $input = $this->service->generateInputFromDto(UploadsTestingDto::class);

        $this->assertNotNull($input);
        $fields = collect($input->config['fields']);

        $this->assertFieldHasDescriptionStrings(
            $fields,
            'oneFile',
            'Formats: jpg, png',
            'Max size: 2048Kb',
            'Image sizes:',
            ImageSizeEnum::BIG->value . ': 1478x731 px',
            ImageSizeEnum::MEDIUM->value . ': 948x639 px',
            ImageSizeEnum::SMALL->value . ': 573x426 px'
        );
        $this->assertFieldNotHasDescriptionStrings(
            $fields,
            'oneFile',
            ImageSizeEnum::BIG->value . ': 500x200 px',
            ImageSizeEnum::MEDIUM->value . ': 200x100 px'
        );

        $this->assertFieldHasDescriptionStrings(
            $fields,
            'filesCollection',
            'Image sizes:',
            ImageSizeEnum::BIG->value . ': 500x200 px',
            ImageSizeEnum::MEDIUM->value . ': 200x100 px'
        );

        $this->assertFieldHasDescriptionStrings(
            $fields,
            'simpleFile',
            'Formats: pdf, doc, docx',
            'Max size: 4096Kb',
        );
        $this->assertFieldNotHasDescriptionStrings(
            $fields,
            'simpleFile',
            'Image sizes:'
        );
    }

    private function assertFieldHasDescriptionStrings(Collection $fields, string $fieldName, ...$strings): void
    {
        $oneFileField = $fields->firstWhere('name', $fieldName);
        $description = $oneFileField['description'];
        $this->assertNotNull($description);

        foreach ($strings as $string) {
            $this->assertStringContainsString($string, $description);
        }
    }

    private function assertFieldNotHasDescriptionStrings(Collection $fields, string $fieldName, ...$strings): void
    {
        $oneFileField = $fields->firstWhere('name', $fieldName);
        $description = $oneFileField['description'];
        $this->assertNotNull($description);

        foreach ($strings as $string) {
            $this->assertStringNotContainsString($string, $description);
        }
    }
}
