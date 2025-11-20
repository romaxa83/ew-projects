<?php

namespace WezomCms\Core\Settings\MetaFields;

use WezomCms\Core\Settings\MultilingualGroup;
use WezomCms\Core\Settings\PageName;
use WezomCms\Core\Settings\RenderSettings;

class SeoFields
{
    /**
     * @param  string|null  $default
     * @param  array  $fields
     * @param  RenderSettings|null  $renderSettings
     * @param  int  $sort
     * @return MultilingualGroup
     * @throws \Exception
     */
    public static function make(
        ?string $default,
        array $fields = [],
        ?RenderSettings $renderSettings = null,
        int $sort = 0
    ) {
        $pageName = PageName::make();
        if ($default) {
            $pageName->default($default);
        }

        return MultilingualGroup::make(
            $renderSettings ?: RenderSettings::siteTab(),
            array_merge(
                [
                    $pageName,
                    Title::make(),
                    Heading::make(),
                    Description::make(),
                    Keywords::make(),
                ],
                $fields
            ),
            $sort
        );
    }
}
