<?php

namespace WezomCms\Core\Traits;

use Spatie\SchemaOrg\BaseType;
use Spatie\SchemaOrg\Organization;
use Spatie\SchemaOrg\Schema;
use WezomCms\Core\Contracts\Assets\AssetManagerInterface;
use WezomCms\Core\Foundation\Helpers;

trait MicroDataTrait
{
    /**
     * @return Organization
     */
    protected function organization()
    {
        $organization = (new Organization())->name(config('app.name'));

        if (Helpers::providerLoaded('WezomCms\Home\HomeServiceProvider')) {
            $organization->url(route('home'));
        }

        $logo = config('cms.core.main.logo.micro_data');
        if (is_file(public_path($logo))) {
            $organization->logo(Schema::imageObject()->url(url($logo)));
        }

        return $organization;
    }

    /**
     * @param  BaseType|string  $schema
     */
    public function renderMicroData($schema)
    {
        app(AssetManagerInterface::class)
            ->addInlineScript(is_object($schema) ? $schema->toScript() : $schema, '', ['type' => 'application/ld+json']);
    }
}
