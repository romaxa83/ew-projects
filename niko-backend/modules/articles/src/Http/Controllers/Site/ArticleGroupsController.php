<?php

namespace WezomCms\Articles\Http\Controllers\Site;

use WezomCms\Articles\Models\ArticleGroup;
use WezomCms\Core\Http\Controllers\SiteController;

class ArticleGroupsController extends SiteController
{
    public function index()
    {
        $settings = settings('article-groups.site', []);

        $pageName = array_get($settings, 'name');

        $result = ArticleGroup::published()
            ->has('publishedArticles')
            ->orderBy('sort')
            ->latest('id')
            ->paginate(array_get($settings, 'limit', 6));

        // Breadcrumbs
        $this->addBreadcrumb($pageName, route('article-groups'));

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

        return view('cms-articles::site.groups.index', [
            'result' => $result
        ]);
    }

    /**
     * @param $slug
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function inner($slug)
    {
        /** @var ArticleGroup $obj */
        $obj = ArticleGroup::publishedWithSlug($slug)->firstOrFail();

        $this->setLangSwitchers($obj, 'article-groups.inner');

        $result = $obj->publishedArticles()
            ->latest('published_at')
            ->latest('id')
            ->paginate(settings('articles.site.limit', 10));

        // Breadcrumbs
        $this->addBreadcrumb(settings('article-groups.site.name'), route('article-groups'));
        $this->addBreadcrumb($obj->name, $obj->getFrontUrl());

        // SEO
        $this->seo()
            ->setTitle($obj->title)
            ->setH1($obj->h1)
            ->setPageName($obj->name)
            ->setDescription($obj->description)
            ->metatags()
            ->setKeywords($obj->keywords)
            ->setNext($result->nextPageUrl())
            ->setPrev($result->previousPageUrl());

        // Render
        return view('cms-articles::site.groups.inner', [
            'result' => $result,
            'currentGroupId' => $obj->id,
        ]);
    }
}
