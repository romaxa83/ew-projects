<?php

namespace WezomCms\Articles\Http\Controllers\Site;

use WezomCms\Articles\Models\Article;
use WezomCms\Core\Http\Controllers\SiteController;

class ArticlesController extends SiteController
{
    public function index()
    {
        $settings = settings('articles.site', []);

        $pageName = array_get($settings, 'name');

        $result = Article::published()
            ->latest('published_at')
            ->latest('id')
            ->paginate(array_get($settings, 'limit', 6));

        // Breadcrumbs
        $this->addBreadcrumb($pageName, route('articles'));

        // SEO
        $this->seo()
            ->setTitle(array_get($settings, 'title'))
            ->setPageName($pageName)
            ->setH1(array_get($settings, 'h1'))
            ->setDescription(array_get($settings, 'description'))
            ->metatags()
            ->setKeywords(array_get($settings, 'keywords'))
            ->setNext($result->nextPageUrl())
            ->setPrev($result->previousPageUrl());

        return view('cms-articles::site.index', compact('result'));
    }

    /**
     * @param $slug
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function inner($slug)
    {
        /** @var Article $obj */
        $obj = Article::publishedWithSlug($slug)->firstOrFail();

        $this->setLangSwitchers($obj, 'articles.inner');

        $useGroups = config('cms.articles.articles.use_groups');

        // Breadcrumbs
        if ($useGroups) {
            $this->addBreadcrumb(settings('article_groups.site.name'), route('article-groups'));
            $this->addBreadcrumb($obj->group->name, $obj->group->getFrontUrl());
        } else {
            $this->addBreadcrumb(settings('articles.site.name'), route('articles'));
        }
        $this->addBreadcrumb($obj->name, $obj->getFrontUrl());

        // SEO
        $this->seo()
            ->setTitle($obj->title)
            ->setH1($obj->h1)
            ->setPageName($obj->name)
            ->setDescription($obj->description)
            ->metatags()
            ->setKeywords($obj->keywords);

        $articlesListLink = $useGroups
            ? route('article-groups.inner', ['slug' => $obj->group->slug])
            : route('articles');

        // Render
        return view('cms-articles::site.inner', compact('obj', 'articlesListLink'));
    }
}
