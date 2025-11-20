<?php

namespace WezomCms\Articles\Widgets;

use Illuminate\Support\Collection;
use WezomCms\Articles\Models\Article;
use WezomCms\Core\Foundation\Widgets\AbstractWidget;

class Latest extends AbstractWidget
{
    /**
     * A list of models that, when changed, will clear the cache of this widget.
     *
     * @var array
     */
    public static $models = [Article::class];

    /**
     * @return array|null
     */
    public function execute(): ?array
    {
        /** @var Collection $result */
        $result = Article::published()
            ->latest('published_at')
            ->latest('id')
            ->limit(array_get($this->data, 'limit', 2))
            ->get();

        if ($result->isEmpty()) {
            return null;
        }

        $linkForMore = (bool) config('cms.articles.articles.use_groups')
            ? route('article-groups')
            : route('articles');

        return compact('result', 'linkForMore');
    }
}
