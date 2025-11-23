@php
    /**
     * @var $categories \Illuminate\Support\Collection|\WezomCms\Catalog\Models\Category[][]
     * @var $currentCategory null|\WezomCms\Catalog\Models\Category
     * @var $defaultIndex null|int
     */
@endphp
<ul>
    @foreach($categories[$defaultIndex] ?? [] as $category)
        @include('cms-catalog::site.partials.expanded-category-row')
    @endforeach
</ul>
