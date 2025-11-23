<?php

namespace WezomCms\Core\Traits;

use Spatie\SchemaOrg\Organization;
use Spatie\SchemaOrg\Schema;
use Spatie\SchemaOrg\Type;
use WezomCms\Core\Contracts\Assets\AssetManagerInterface;
use WezomCms\Core\Foundation\Helpers;
use WezomCms\Home\HomeServiceProvider;

trait MicroDataTrait
{
    /**
     * @return Organization
     */
    protected function organization()
    {
        $organization = (new Organization())->name(config('app.name'));

        if (Helpers::providerLoaded(HomeServiceProvider::class)) {
            $organization->url(route('home'));
        }

        $logo = config('cms.core.main.logo.micro_data');
        if (is_file(public_path($logo))) {
            $organization->logo(Schema::imageObject()->url(url($logo)));
        }

        return $organization;
    }

    /**
     * @param  Type|string  $schema
     */
    public function renderMicroData($schema)
    {
        if (is_object($schema)) {
            $schema = $schema instanceof Type ? $schema->toScript() : json_encode($schema);
        }

        app(AssetManagerInterface::class)
            ->addInlineScript($schema, '', ['type' => 'application/ld+json'])
            ->group(AssetManagerInterface::GROUP_SITE);
    }
}
