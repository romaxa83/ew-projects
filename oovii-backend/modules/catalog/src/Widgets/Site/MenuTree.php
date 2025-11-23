<?php

namespace WezomCms\Catalog\Widgets\Site;

use Illuminate\Support\Collection;
use WezomCms\Catalog\Models\Category;
use WezomCms\Core\Foundation\Widgets\AbstractWidget;
use WezomCms\Core\Models\Setting;

class MenuTree extends AbstractWidget
{
    /**
     * A list of models that, when changed, will clear the cache of this widget.
     *
     * @var array
     */
    public static $models = [Category::class, Setting::class];

    /**
     * @return array|null
     */
    public function execute(): ?array
    {
        $categories = $this->getCategories();

        if (!($this->parameter('force_render', false)) && $categories->isEmpty()) {
            return null;
        }

        $showMore = $categories->count() > settings('categories.site.categories_menu_limit', 12);

        return compact('categories', 'showMore');
    }

    /**
     * @return Collection
     */
    private function getCategories()
    {
        return Category::published()
            ->whereNull('parent_id')
            ->sorting()
            ->with([
                'children' => function ($query) {
                    $query->published()
                        ->sorting()
                        ->with([
                            'children' => function ($query) {
                                $query->published()
                                    ->sorting();
                            }
                        ]);
                }
            ])
            ->limit(settings('categories.site.categories_menu_limit', 12))
            ->get();
    }
}
