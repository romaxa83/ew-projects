<?php

declare(strict_types=1);

namespace Wezom\Core\Tests\Unit\GraphQL\Projections;

use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use Wezom\Core\Testing\Projections\FileProjection;
use Wezom\Core\Testing\TestCase;

class ProjectionBuilderTest extends TestCase
{
    #[Test]
    public function makeProjection(): void
    {
        $root = SomeRootProjection::root()
            ->with(SomeRelationProjection::root())
            ->with(SomeRelationProjection::collection())
            ->with(SomeRelationProjection::root('customRelationName'))
            ->with(SomeTranslationProjection::root())
            ->with(SomeTranslationProjection::collection())
            ->with(SomeTranslationProjection::root('customTranslationName'))
            ->get();

        self::assertEquals(
            [
                'id',
                'name',
                'someRelation' => [
                    'id',
                    'relationField'
                ],
                'someRelations' => [
                    'id',
                    'relationField'
                ],
                'customRelationName' => [
                    'id',
                    'relationField'
                ],
                'translation' => [
                    'id',
                    'translationField'
                ],
                'translations' => [
                    'id',
                    'translationField'
                ],
                'customTranslationName' => [
                    'id',
                    'translationField'
                ]
            ],
            $root
        );
    }

    #[Test]
    public function makeNestedProjection(): void
    {
        $root = SomeRootProjection::root()
            ->with(
                SomeRelationProjection::root()
                    ->with(SomeTranslationProjection::root())
            )
            ->with(SomeTranslationProjection::root())
            ->with(
                SomeRelationProjection::pagination('pagination')
                    ->with(SomeTranslationProjection::root())
            );

        self::assertEquals(
            [
                'id',
                'name',
                'someRelation' => [
                    'id',
                    'relationField',
                    'translation' => [
                        'id',
                        'translationField',
                    ],
                ],
                'translation' => [
                    'id',
                    'translationField',
                ],
                'pagination' => [
                    'data' => [
                        'id',
                        'relationField',
                        'translation' => [
                            'id',
                            'translationField',
                        ],
                    ]
                ]
            ],
            $root->get(),
        );
    }

    #[Test]
    public function resolveTranslationProjection(): void
    {
        $projection = SomeProjection::root()
            ->withTranslation()
            ->withTranslations()
            ->get();

        self::assertEquals(
            [
                'id',
                'name',
                'translation' => [
                    'id',
                    'translationField',
                ],
                'translations' => [
                    'id',
                    'translationField',
                ],
            ],
            $projection
        );
    }

    #[Test]
    public function resolveTranslationProjectionFailedWhenTranslationNotFound(): void
    {
        self::expectException(RuntimeException::class);
        self::expectExceptionMessage(
            sprintf(
                'Translation projection class "%s" does not exists for projection "%s"',
                'SomeRelationTranslationProjection',
                SomeRelationProjection::class,
            )
        );

        SomeRelationProjection::root()->withTranslation();
    }

    #[Test]
    public function makeWithFileProjection(): void
    {
        $projection = SomeProjection::root()
            ->withFile('images')
            ->get();

        self::assertEquals(
            [
                'id',
                'name',
                'images' => FileProjection::root()->get(),
            ],
            $projection
        );
    }

    #[Test]
    public function makeWithIdProjection(): void
    {
        $projection = SomeProjection::root()
            ->withIdProjection('relation')
            ->get();

        self::assertEquals(
            [
                'id',
                'name',
                'relation' => [
                    'id',
                ],
            ],
            $projection
        );
    }

    #[Test]
    public function makeWithIdProjectionWithNested(): void
    {
        $projection = SomeProjection::root()
            ->withIdProjection(
                'relation',
                SomeRelationProjection::root(),
                SomeRootProjection::root()->with(SomeProjection::root())
            )
            ->get();

        self::assertEquals(
            [
                'id',
                'name',
                'relation' => [
                    'id',
                    'someRelation' => [
                        'id',
                        'relationField',
                    ],
                    'someRoot' => [
                        'id',
                        'name',
                        'some' => [
                            'id',
                            'name',
                        ],
                    ],
                ],
            ],
            $projection
        );
    }
}
