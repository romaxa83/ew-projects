<?php

namespace WezomCms\Pages;

use Illuminate\Database\Eloquent\Collection;
use WezomCms\Core\BaseServiceProvider;
use WezomCms\Core\Contracts\PermissionsContainerInterface;
use WezomCms\Core\Contracts\SitemapXmlGeneratorInterface;
use WezomCms\Core\Traits\SidebarMenuGroupsTrait;
use WezomCms\Pages\Models\Page;

class PagesServiceProvider extends BaseServiceProvider
{
    use SidebarMenuGroupsTrait;

    /**
     * @param  PermissionsContainerInterface  $permissions
     */
    public function permissions(PermissionsContainerInterface $permissions)
    {
        $permissions->add('pages', __('cms-pages::admin.Pages'))->withEditSettings();
    }

    public function adminMenu()
    {
        $this->contentGroup()
            ->add(__('cms-pages::admin.Pages'), route('admin.pages.index'))
            ->data('permission', 'pages.view')
            ->data('icon', 'fa-file-text-o')
            ->nickname('pages');
    }

    /**
     * @return array
     */
    public function sitemap()
    {
        /** @var Collection $pages */
        $pages = Page::published()
            ->select('id')
            ->latest('id')
            ->get()
            ->map(function (Page $page) {
                return [
                    'sort' => 10,
                    'parent_id' => 0,
                    'name' => $page->name,
                    'url' => $page->getFrontUrl(),
                ];
            });

        return $pages->toArray();
    }

    /**
     * @param  SitemapXmlGeneratorInterface  $sitemap
     * @throws \ErrorException
     */
    public function sitemapXml(SitemapXmlGeneratorInterface $sitemap)
    {
        $sitemap->add(function () {
            return Page::published()
                ->select('id')
                ->get()
                ->mapWithKeys(function (Page $page) {
                    return [$page->id => $page->getFrontUrl()];
                });
        });
    }
}
