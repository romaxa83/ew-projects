<?php

namespace WezomCms\Articles\Dashboards;

use WezomCms\Articles\Models\Article;
use WezomCms\Core\Foundation\Dashboard\AbstractValueDashboard;

class ArticlesDashboard extends AbstractValueDashboard
{
    /**
     * @var null|int - cache time in minutes.
     */
    protected $cacheTime = 5;

    /**
     * @var null|string - permission for link
     */
    protected $ability = 'articles.view';

    /**
     * @return int
     */
    public function value(): int
    {
        return Article::count();
    }

    /**
     * @return string
     */
    public function description(): string
    {
        return __('cms-articles::admin.Articles');
    }

    /**
     * @return string
     */
    public function icon(): string
    {
        return 'fa-newspaper-o';
    }

    /**
     * @return string
     */
    public function iconColorClass(): string
    {
        return 'color-warning';
    }

    /**
     * @return null|string
     */
    public function url(): ?string
    {
        return route('admin.articles.index');
    }
}
