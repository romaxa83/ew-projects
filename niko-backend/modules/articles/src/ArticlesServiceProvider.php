<?php

namespace WezomCms\Articles;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use WezomCms\Articles\Models\Article;
use WezomCms\Articles\Models\ArticleGroup;
use WezomCms\Core\BaseServiceProvider;
use WezomCms\Core\Contracts\PermissionsContainerInterface;
use WezomCms\Core\Contracts\SitemapXmlGeneratorInterface;
use WezomCms\Core\Traits\SidebarMenuGroupsTrait;

class ArticlesServiceProvider extends BaseServiceProvider
{
    use SidebarMenuGroupsTrait;

    /**
     * All module widgets.
     *
     * @var array|string|null
     */
    protected $widgets = 'cms.articles.articles.widgets';

    /**
     * Dashboard widgets.
     *
     * @var array|string|null
     */
    protected $dashboard = 'cms.articles.articles.dashboards';

    /**
     * @param  PermissionsContainerInterface  $permissions
     */
    public function permissions(PermissionsContainerInterface $permissions)
    {
        $permissions->add('articles', __('cms-articles::admin.Articles'))->withEditSettings();

        if (config('cms.articles.articles.use_groups')) {
            $permissions->add('article-groups', __('cms-articles::admin.Article groups'))->withEditSettings();
        }
    }

    /**
     * Register all admin sidebar menu links.
     */
    public function adminMenu()
    {
//        $group = $this->contentGroup()
//            ->add(__('cms-articles::admin.Articles'), route('admin.articles.index'))
//            ->data('icon', 'fa-briefcase')
//            ->nickname('articles');
//
//        if (config('cms.articles.articles.use_groups')) {
//            $group->add(__('cms-articles::admin.Articles'), route('admin.articles.index'))
//                ->data('icon', 'fa-list')
//                ->data('permission', 'articles.view')
//                ->data('position', 1);
//
//            $group->add(__('cms-articles::admin.Groups'), route('admin.article-groups.index'))
//                ->data('icon', 'fa-th-large')
//                ->data('permission', 'article-groups.view')
//                ->data('position', 2);
//        } else {
//            $group->data('permission', 'articles.view');
//        }
    }

    /**
     * @return array
     */
    public function sitemap()
    {
        $userGroups = config('cms.articles.articles.use_groups');

        $root = [
            'id' => 'articles',
            'sort' => 6,
            'parent_id' => 0,
            'name' => settings('article-groups.site.name', __('cms-articles::site.Articles')),
            'url' => $userGroups ? route('article-groups') : route('articles'),
        ];

        if (config('cms.articles.articles.sitemap.articles')) {
            if ($userGroups) {
                $articleGroups = ArticleGroup::published()
                    ->select('id')
                    ->with('translations:name,slug,locale,article_group_id')
                    ->with(['articles' => function ($query) {
                        /** @var Builder $query */
                        $query->published()
                            ->select('id', 'article_group_id')
                            ->with('translations:name,slug,locale,article_id');
                    }])
                    ->latest('id')
                    ->get()
                    ->map(function (ArticleGroup $group) {
                        $articleGroup = collect([
                            'parent_id' => 'articles',
                            'id' => 'article_group-' . $group->id,
                            'name' => $group->name,
                            'url' => $group->getFrontUrl(),
                        ]);

                        $articles = $group->articles->map(function (Article $item) {
                            return [
                                'parent_id' => 'article_group-' . $item->article_group_id,
                                'id' => 'article-' . $item->id,
                                'name' => $item->name,
                                'url' => $item->getFrontUrl(),
                            ];
                        });

                        return $articles->prepend($articleGroup)->toArray();
                    });

                return $articleGroups->flatten(1)->prepend($root)->toArray();
            } else {
                /** @var Collection $articles */
                $articles = Article::published()
                    ->select('id')
                    ->with('translations:name,slug,locale,article_id')
                    ->latest('id')
                    ->get()
                    ->map(function (Article $item) {
                        return [
                            'parent_id' => 'articles',
                            'id' => $item->id,
                            'name' => $item->name,
                            'url' => $item->getFrontUrl(),
                        ];
                    });

                return $articles->prepend($root)->toArray();
            }
        } else {
            return [$root];
        }
    }

    /**
     * @param  SitemapXmlGeneratorInterface  $sitemap
     * @throws \ErrorException
     */
    public function sitemapXml(SitemapXmlGeneratorInterface $sitemap)
    {
        $sitemap->add(route('article-groups'));

        Article::published()
            ->select('id')
            ->with('translations:slug,locale,article_id')
            ->latest('id')
            ->get()
            ->each(function (Article $item) use ($sitemap) {
                $sitemap->add($item->getFrontUrl());
            });
    }
}
