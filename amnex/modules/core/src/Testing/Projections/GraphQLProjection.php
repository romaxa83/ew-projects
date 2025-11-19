<?php

declare(strict_types=1);

namespace Wezom\Core\Testing\Projections;

abstract class GraphQLProjection
{
    /**
     * General fields
     */
    public const ID_FIELD = 'id';

    public const UUID_FIELD = 'uuid';
    public const NAME_FIELD = 'name';
    public const SORT_FIELD = 'sort';
    public const CREATED_AT_FIELD = 'createdAt';
    public const UPDATED_AT_FIELD = 'updatedAt';
    public const DELETED_AT_FIELD = 'deletedAt';
    public const SEO_IMAGE_TITLE_FIELD = 'imageTitle';
    public const SEO_IMAGE_ALT_FIELD = 'imageAlt';

    /**
     * Seo fields
     */
    public const SEO_H1_FIELD = 'h1';

    public const SEO_TITLE_FIELD = 'title';
    public const SEO_DESCRIPTION_FIELD = 'description';
    public const SEO_TEXT_FIELD = 'text';

    /**
     * Media fields
     */
    public const SIZE_FIELD = 'size';

    public const MIME_TYPE_FIELD = 'mimeType';
    public const ORIGINAL_URL_FIELD = 'originalUrl';
    public const SPECIAL_URL_FIELD = 'specialUrl';
    public const CONVERSIONS_FIELD = 'conversions';
    public const URL_FIELD = 'url';
    public const MESSAGE_FIELD = 'message';
    public const TYPE_FIELD = 'type';
}
