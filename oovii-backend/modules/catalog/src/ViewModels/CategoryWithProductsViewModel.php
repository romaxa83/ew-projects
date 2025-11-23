<?php

namespace WezomCms\Catalog\ViewModels;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use WezomCms\Catalog\Filter\Contracts\FilterInterface;
use WezomCms\Catalog\Filter\Contracts\SortInterface;
use WezomCms\Catalog\Models\CatalogSeoTemplate;
use WezomCms\Catalog\Models\Category;
use WezomCms\Core\Traits\LangSwitchingGenerator;

class CategoryWithProductsViewModel extends ProductsListViewModel
{
    use LangSwitchingGenerator;

    /**
     * @var Category
     */
    public $category;

    /**
     * CategoryViewModel constructor.
     * @param  Category  $category
     * @param  Request  $request
     * @param Paginator $products
     * @param  FilterInterface  $filter
     * @param  SortInterface  $sort
     */
    public function __construct(
        Category $category,
        Request $request,
        $products,
        FilterInterface $filter,
        SortInterface $sort
    ) {
        $this->category = $category;

        parent::__construct($request, $products, $filter, $sort);

        // Seo
        $this->setSeo();
    }

    /**
     * @inheritDoc
     */
    public function baseUrl(): string
    {
        return $this->category->getFrontUrl();
    }

    /**
     * Generate seo meta attributes
     */
    private function setSeo()
    {
        $title = $this->category->title;
        $h1 = $this->category->h1;
        $description = $this->category->description;
        $keywords = $this->category->keywords;
        $seoText = $this->category->text;

        $seoTemplate = $this->applySeoTemplate($title, $h1, $keywords, $description, $seoText);

        list($title, $h1, $keywords, $description) = CatalogSeoTemplate::applyDefaultTemplate(
            $this->category,
            $title,
            $h1,
            $keywords,
            $description
        );

        $title = $title ?: $this->category->name;
        if (!$seoTemplate) {
            /** @var Collection $selectedAttributes */
            $selectedAttributes = $this->filter->getSelectedAttributes()->map(function ($item) {
                return array_get($item, 'name');
            })->filter();

            if ($selectedAttributes->isNotEmpty()) {
                $title .= ' - ' . $selectedAttributes->implode(', ');
            }

            if ($this->sort->urlHasSortKey() || $this->filter->getUrlBuilder()->getParameters()) {
                $this->seo()
                    ->setCanonical($this->category->getFrontUrl())
                    ->metatags()
                    ->addMeta('robots', 'noindex, nofollow');
            }

            if ($this->products->currentPage() > 1) {
                $this->seo()
                    ->setSeoText(null)
                    ->metatags()
                    ->setDescription(null);
            }
        } else {
            $this->seo()->setCanonical($this->products->url(1));
        }

        if ($this->sort->urlHasSortKey()) {
            $title .= " - {$this->sort->getCurrentSortName()}";
        }

        // SEO
        $this->seo()
            ->setTitle($title)
            ->setPageName($this->category->name)
            ->setH1($h1)
            ->setSeoText($seoText)
            ->setDescription($description)
            ->setPrevNext($this->products)
            ->metatags()
            ->setKeywords($keywords);

        if ($this->sort->urlHasSortKey()) {
            $this->seo()
                ->setSeoText(null)
                ->metatags()
                ->setDescription(null);
        }

        // Lang switchers
        if ($this->request->route()->getName() === 'catalog.category.filter') {
            $this->setLangSwitchers(
                $this->category,
                'catalog.category.filter',
                ['slug' => 'slug', 'id' => 'model.id', 'not-model' => ['filter' => $this->request->route('filter')]]
            );
        } else {
            $this->setLangSwitchers($this->category, 'catalog.category', ['slug' => 'slug', 'id' => 'model.id']);
        }
    }

    /**
     * Search and apply seo template by selected filter parameters
     * @param  string|null  $title
     * @param  string|null  $h1
     * @param  string|null  $keywords
     * @param  string|null  $description
     * @param  string|null  $seoText
     * @return CatalogSeoTemplate|null
     */
    private function applySeoTemplate(
        ?string &$title,
        ?string &$h1,
        ?string &$keywords,
        ?string &$description,
        ?string &$seoText
    ): ?CatalogSeoTemplate {
        /** @var Collection $groupedAttributes */
        $groupedAttributes = $this->filter->getSelectedAttributes()
            ->mapToGroups(function ($item) {
                return [$item['group'] => $item['name']];
            });

        /** @var CatalogSeoTemplate|null $seoTemplate */
        $seoTemplate = CatalogSeoTemplate::searchTemplateByParameters($this->category, $groupedAttributes->keys());
        if ($seoTemplate) {
            $groupedAttributes->put('category', collect($this->category->name));

            $search = $groupedAttributes->map(function ($item, $key) {
                return '[' . $key . ']';
            })->toArray();

            $replace = $groupedAttributes->map(function (Collection $values) {
                return $values->implode(', ');
            })->toArray();

            if (!$title && $seoTemplate->title) {
                $title = str_replace($search, $replace, $seoTemplate->title);
            }

            if (!$h1 && $seoTemplate->h1) {
                $h1 = str_replace($search, $replace, $seoTemplate->h1);
            }

            if (!$description && $seoTemplate->description) {
                $description = str_replace($search, $replace, $seoTemplate->description);
            }

            if (!$keywords && $seoTemplate->keywords) {
                $keywords = str_replace($search, $replace, $seoTemplate->keywords);
            }

            if (!$seoText && $seoTemplate->text) {
                $seoText = str_replace($search, $replace, $seoTemplate->text);
            }
        }

        return $seoTemplate;
    }
}
